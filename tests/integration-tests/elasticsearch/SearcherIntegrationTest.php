<?php
namespace elasticsearch;

class SearcherIntegrationTest extends BaseIntegrationTestCase
{
	public function setUp()
	{
		parent::setUp();

		$this->searcher = new Searcher();
	}

	/** reproduces issue #25 */
	public function testDateScored()
	{
		update_option('score_field_post_date', 1);

		register_post_type('post');

		Indexer::addOrUpdate((object) array(
			'post_type' => 'post',
			'ID' => 1,
			'post_date' => '10/24/1988 00:00:00 CST',
			'field1' => 'value1',
			'field2' => 'value2'
		));

		Indexer::addOrUpdate((object) array(
			'post_type' => 'post',
			'ID' => 2,
			'field1' => 'value2',
			'post_date' => '10/24/1988 00:00:00 CST',
			'field2' => 'value1'
		));

		$this->index->refresh();

		$results = $this->searcher->search('value1');
	}

	public function testScoreMultisite()
	{
		global $blog_id;

		update_option('fields', array('field1' => 1));
		update_option('score_field_field1', 1);

		register_post_type('post');

		$blog_id = 1;

		Indexer::addOrUpdate((object) array(
			'post_type' => 'post',
			'ID' => 1,
			'post_date' => '10/24/1988 00:00:00 CST',
			'field1' => 'value1'
		));

		$blog_id = 2;

		Indexer::addOrUpdate((object) array(
			'post_type' => 'post',
			'ID' => 2,
			'field1' => 'value1',
			'post_date' => '10/24/1988 00:00:00 CST'
		));

		$this->index->refresh();

		$results = $this->searcher->search('value1');

		$this->assertEquals(1, $results['total']);
		$this->assertEquals(array(2), $results['ids']);

		$blog_id = 1;

		$results = $this->searcher->search('value1');

		$this->assertEquals(1, $results['total']);
		$this->assertEquals(array(1), $results['ids']);
	}

	public function testSearchName()
	{
		global $blog_id;
		
		update_option('score_tax_tag', 1);

		register_post_type('post');
		register_taxonomy('tag', 'post');

		wp_insert_term('John Doe', 'tag', array(
  			'slug' => 'name-game-1'
  		));

  		wp_insert_term('Jane Snow', 'tag', array(
  			'slug' => 'name-game-2'
  		));

  		wp_set_object_terms(1, array(2), 'tag');
  		wp_set_object_terms(2, array(1, 2), 'tag');

  		Indexer::clear();

		Indexer::addOrUpdate((object) array(
			'post_type' => 'post',
			'post_date' => '10/24/1988 00:00:00 CST',
			'ID' => 1
		));

		Indexer::addOrUpdate((object) array(
			'post_type' => 'post',
			'post_date' => '10/24/1988 00:00:00 CST',
			'ID' => 2
		));

		$this->index->refresh();

		$results = $this->searcher->search('name', 0, 10);

		$this->assertEquals(0, $results['total']);

		$results = $this->searcher->search('jane', 0, 10);

		$this->assertEquals(2, $results['total']);
		$this->assertEquals(0, count(array_diff(array(1, 2), $results['ids'])));
		$this->assertEquals(array('tag' => array('name-game-1' => 1, 'name-game-2' => 2)), $results['facets']);

		$results = $this->searcher->search('john', 0, 10);

		$this->assertEquals(1, $results['total']);
		$this->assertEquals(array(2), $results['ids']);
		$this->assertEquals(array('tag' => array('name-game-1' => 1, 'name-game-2' => 1)), $results['facets']);
	}

	public function testSearchTaxonomiesHyphens()
	{
		global $blog_id;
		
		update_option('score_tax_tag', 1);

		register_post_type('post');
		register_taxonomy('tag', 'post');

		wp_insert_term('Tag 1', 'tag', array(
  			'slug' => 'tag-flag-1'
  		));

  		wp_insert_term('Tag 2', 'tag', array(
  			'slug' => 'name-game-2'
  		));

  		wp_set_object_terms(1, array(1, 2), 'tag');
  		wp_set_object_terms(2, array(1, 2), 'tag');

  		Indexer::clear();

		Indexer::addOrUpdate((object) array(
			'post_type' => 'post',
			'post_date' => '10/24/1988 00:00:00 CST',
			'ID' => 1
		));

		Indexer::addOrUpdate((object) array(
			'post_type' => 'post',
			'post_date' => '10/24/1988 00:00:00 CST',
			'ID' => 2
		));

		$this->index->refresh();

		$results = $this->searcher->search(null, 0, 10, array('tag' => 'tag-flag-1'));

		$this->assertEquals(2, $results['total']);
		$this->assertEquals(0, count(array_diff(array(1, 2), $results['ids'])));
		$this->assertEquals(array('tag' => array('name-game-2' => 2, 'tag-flag-1' => 2)), $results['facets']);

		$results = $this->searcher->search(null, 0, 10, array('tag' => 'name'));

		$this->assertEquals(0, $results['total']);
		$this->assertEquals(array(), $results['ids']);
		$this->assertEquals(array(), $results['facets']);
	}

	public function testSearchTaxonomiesMultisite()
	{
		global $blog_id;
		
		update_option('score_tax_tag', 1);

		register_post_type('post');
		register_taxonomy('tag', 'post');

		wp_insert_term('Tag 1', 'tag', array(
  			'slug' => 'tag1'
  		));

  		wp_insert_term('Tag 2', 'tag', array(
  			'slug' => 'tag2'
  		));

  		wp_set_object_terms(1, array(1), 'tag');
		wp_set_object_terms(2, array(1), 'tag');

		$blog_id = 1;

		Indexer::addOrUpdate((object) array(
			'post_type' => 'post',
			'post_date' => '10/24/1988 00:00:00 CST',
			'ID' => 1
		));

		$blog_id = 2;

		Indexer::addOrUpdate((object) array(
			'post_type' => 'post',
			'post_date' => '10/24/1988 00:00:00 CST',
			'ID' => 2
		));

		$this->index->refresh();

		$results = $this->searcher->search(null, 0, 10, array('tag' => 'tag1'));

		$this->assertEquals(1, $results['total']);
		$this->assertEquals(array(2), $results['ids']);
		$this->assertEquals(array('tag' => array('tag1' => 1)), $results['facets']);
	}

	public function testIgnoreHtml()
	{
		update_option('fields', array('post_content' => 1));
		update_option('score_field_post_content', 1);

		register_post_type('post');

		Indexer::clear();

		Indexer::addOrUpdate((object) array(
			'post_type' => 'post',
			'ID' => 1,
			'post_date' => '10/24/1988 00:00:00 CST',
			'post_content' => '<html>yayzer is my <span class="foo">text</span></html>'
		));

		$this->index->refresh();

		$results = $this->searcher->search('html');

		$this->assertEquals(0, $results['total']);

		$results = $this->searcher->search('foo');

		$this->assertEquals(0, $results['total']);

		$results = $this->searcher->search('span');

		$this->assertEquals(0, $results['total']);

		$results = $this->searcher->search('yayzer');

		$this->assertEquals(1, $results['total']);
	}

	public function testExact()
	{
		update_option('fields', array('field1' => 1));
		update_option('score_field_field1', 1);

		register_post_type('post');

		Indexer::clear();

		Indexer::addOrUpdate((object) array(
			'post_type' => 'post',
			'ID' => 1,
			'post_date' => '10/24/1988 00:00:00 CST',
			'field1' => 'The quick brown fox jumps over the lazy dog'
		));

		$this->index->refresh();

		$results = $this->searcher->search('quick fox brown');

		$this->assertEquals(1, $results['total']);
		$this->assertEquals(array(1), $results['ids']);

		$results = $this->searcher->search('"quick brown fox"');

		$this->assertEquals(1, $results['total']);
		$this->assertEquals(array(1), $results['ids']);

		$results = $this->searcher->search('"quick brown fox" lazy');

		$this->assertEquals(1, $results['total']);
		$this->assertEquals(array(1), $results['ids']);
	}

	public function testBoolean()
	{
		update_option('fields', array('field1' => 1));
		update_option('score_field_field1', 1);

		register_post_type('post');

		Indexer::clear();

		Indexer::addOrUpdate((object) array(
			'post_type' => 'post',
			'ID' => 1,
			'post_date' => '10/24/1988 00:00:00 CST',
			'field1' => 'The quick brown fox jumps over the lazy dog'
		));

		Indexer::addOrUpdate((object) array(
			'post_type' => 'post',
			'ID' => 2,
			'post_date' => '10/24/1988 00:00:00 CST',
			'field1' => 'The quick yellow fox jumps over the fun dog'
		));

		$this->index->refresh();

		$results = $this->searcher->search('quick and brown');

		$this->assertEquals(1, $results['total']);
		$this->assertEquals(array(1), $results['ids']);

		$results = $this->searcher->search('quick AND brown');

		$this->assertEquals(1, $results['total']);
		$this->assertEquals(array(1), $results['ids']);

		$results = $this->searcher->search('quick And brown');

		$this->assertEquals(1, $results['total']);
		$this->assertEquals(array(1), $results['ids']);

		$results = $this->searcher->search('brown or yellow');

		$this->assertEquals(2, $results['total']);
		$this->assertEquals(0, count(array_diff(array(1, 2), $results['ids'])));

		$results = $this->searcher->search('brown OR yellow');

		$this->assertEquals(2, $results['total']);
		$this->assertEquals(0, count(array_diff(array(1, 2), $results['ids'])));

		$results = $this->searcher->search('brown Or yellow');

		$this->assertEquals(2, $results['total']);
		$this->assertEquals(0, count(array_diff(array(1, 2), $results['ids'])));
	}

	public function testFuzzy()
	{
		if($this->isVersion('0.90.2')){
			update_option('fields', array('field1' => 1));
			update_option('score_field_field1', 2);

			register_post_type('post');

			Indexer::clear();

			Indexer::addOrUpdate((object) array(
				'post_type' => 'post',
				'ID' => 1,
				'post_date' => '10/24/1988 00:00:00 CST',
				'field1' => 'Another Post with Everything In It'
			));
		
			Indexer::addOrUpdate((object) array(
				'post_type' => 'post',
				'ID' => 2,
				'post_date' => '10/24/1988 00:00:00 CST',
				'field1' => 'some other thang'
			));

			$this->index->refresh();

			update_option('fuzzy', 1);
			$results = $this->searcher->search('anothar~');
			$this->assertEquals(1, $results['total']);

			update_option('fuzzy', 1);
			$results = $this->searcher->search('bnothar~');
			$this->assertEquals(0, $results['total']);

			update_option('fuzzy', 2);
			$results = $this->searcher->search('bnothar~');
			$this->assertEquals(1, $results['total']);

			update_option('fuzzy', 2);
			$results = $this->searcher->search('baothar~');
			$this->assertEquals(0, $results['total']);
		}
	}

	public function testAnalyzed()
	{
		update_option('fields', array('field1' => 1));
		update_option('score_field_field1', 1);

		register_post_type('post');

		Indexer::clear();

		Indexer::addOrUpdate((object) array(
			'post_type' => 'post',
			'ID' => 1,
			'post_date' => '10/24/1988 00:00:00 CST',
			'field1' => 'foo bar'
		));

		$this->index->refresh();

		$results = $this->searcher->search('foo');

		$this->assertEquals(1, $results['total']);
		$this->assertEquals(array(1), $results['ids']);

		$results = $this->searcher->search('foo bar');

		$this->assertEquals(1, $results['total']);
		$this->assertEquals(array(1), $results['ids']);

		$results = $this->searcher->search('"foo bar"');

		$this->assertEquals(1, $results['total']);
		$this->assertEquals(array(1), $results['ids']);
	}

	public function testNotAnalyzed()
	{
		update_option('fields', array('field1' => 1));
		update_option('not_analyzed', array('field1' => 1));
		update_option('score_field_field1', 1);

		register_post_type('post');

		Indexer::clear();

		Indexer::addOrUpdate((object) array(
			'post_type' => 'post',
			'ID' => 1,
			'post_date' => '10/24/1988 00:00:00 CST',
			'field1' => 'foo bar'
		));

		$this->index->refresh();

		$results = $this->searcher->search('foo');

		$this->assertEquals(0, $results['total']);
		$this->assertEquals(array(), $results['ids']);

		$results = $this->searcher->search('foo bar');

		$this->assertEquals(0, $results['total']);
		$this->assertEquals(array(), $results['ids']);

		$results = $this->searcher->search('"foo bar"');

		$this->assertEquals(1, $results['total']);
		$this->assertEquals(array(1), $results['ids']);
	}

	public function testScoreSort()
	{
		update_option('fields', array('field1' => 1, 'field2' => 1));
		update_option('score_field_field1', 1);
		update_option('score_field_field2', 2);

		register_post_type('post');

		Indexer::addOrUpdate((object) array(
			'post_type' => 'post',
			'ID' => 1,
			'post_date' => '10/24/1988 00:00:00 CST',
			'field1' => 'value1',
			'field2' => 'value2'
		));

		Indexer::addOrUpdate((object) array(
			'post_type' => 'post',
			'ID' => 2,
			'field1' => 'value2',
			'post_date' => '10/24/1988 00:00:00 CST',
			'field2' => 'value1'
		));

		$this->index->refresh();

		$results = $this->searcher->search('value1');

		$this->assertEquals(2, $results['total']);
		$this->assertEquals(0, count(array_diff(array(1, 2), $results['ids'])));

		$results = $this->searcher->search('value2');

		$this->assertEquals(2, $results['total']);
		$this->assertEquals(0, count(array_diff(array(1, 2), $results['ids'])));

		$results = $this->searcher->search('value1 value2');

		$this->assertEquals(2, $results['total']);
		$this->assertEquals(0, count(array_diff(array(1, 2), $results['ids'])));
	}

	public function testSearchNotScored()
	{
		update_option('fields', array('field1' => 1, 'field2' => 1));

		register_post_type('post');

		Indexer::addOrUpdate((object) array(
			'post_type' => 'post',
			'ID' => 1,
			'post_date' => '10/24/1988 00:00:00 CST',
			'field1' => 'value1',
			'field2' => 'value2'
		));

		Indexer::addOrUpdate((object) array(
			'post_type' => 'post',
			'ID' => 2,
			'post_date' => '10/24/1988 00:00:00 CST',
			'field1' => 'value2',
			'field2' => 'value3'
		));

		$this->index->refresh();

		$results = $this->searcher->search('value1');

		$this->assertEquals(0, $results['total']);
		$this->assertEquals(array(), $results['ids']);
	}

	public function testSearchFields()
	{
		update_option('fields', array('field1' => 1, 'field2' => 1));
		update_option('score_field_field1', 1);
		update_option('score_field_field2', 1);

		register_post_type('post');

		Indexer::addOrUpdate((object) array(
			'post_type' => 'post',
			'ID' => 1,
			'post_date' => '10/24/1988 00:00:00 CST',
			'field1' => 'value1',
			'field2' => 'value2'
		));

		Indexer::addOrUpdate((object) array(
			'post_type' => 'post',
			'ID' => 2,
			'post_date' => '10/24/1988 00:00:00 CST',
			'field1' => 'value2',
			'field2' => 'value3'
		));

		$this->index->refresh();

		$results = $this->searcher->search('value1');

		$this->assertEquals(1, $results['total']);
		$this->assertEquals(array(1), $results['ids']);

		$results = $this->searcher->search('value2');

		$this->assertEquals(2, $results['total']);
		$this->assertEquals(0, count(array_diff(array(1, 2), $results['ids'])));

		$results = $this->searcher->search('value1 value3');

		$this->assertEquals(2, $results['total']);
		$this->assertEquals(0, count(array_diff(array(1, 2), $results['ids'])));

		$results = $this->searcher->search('value3');

		$this->assertEquals(1, $results['total']);
		$this->assertEquals(array(2), $results['ids']);

		$results = $this->searcher->search('value4');

		$this->assertEquals(0, $results['total']);
		$this->assertEquals(array(), $results['ids']);
	}

	public function testSearchPaging()
	{
		update_option('fields', array('field1' => 1));
		update_option('score_field_field1', 1);

		register_post_type('post');

		Indexer::addOrUpdate((object) array(
			'post_type' => 'post',
			'ID' => 1,
			'post_date' => '10/24/1988 00:00:00 CST',
			'field1' => 'value1',
		));

		Indexer::addOrUpdate((object) array(
			'post_type' => 'post',
			'ID' => 2,
			'post_date' => '10/21/1988 00:00:00 CST',
			'field1' => 'value1',
		));

		Indexer::addOrUpdate((object) array(
			'post_type' => 'post',
			'ID' => 3,
			'post_date' => '10/20/1988 00:00:00 CST',
			'field1' => 'value1',
		));

		$this->index->refresh();

		$results = $this->searcher->search('value1', 0, 1);

		$this->assertEquals(3, $results['total']);
		$first = $results['ids'][0];

		$results = $this->searcher->search('value1', 1, 1);

		$this->assertEquals(3, $results['total']);

		$second = $results['ids'][0];
		$this->assertTrue($second != $first);

		$results = $this->searcher->search('value1', 2, 1);

		$this->assertEquals(3, $results['total']);
		$this->assertTrue($results['ids'][0] != $first && $results['ids'][0] != $second);
	}

	public function testSearchTaxonomies()
	{
		update_option('score_tax_tag', 1);

		register_post_type('post');
		register_taxonomy('tag', 'post');

		wp_insert_term('Tag 1', 'tag', array(
  			'slug' => 'tag1'
  		));

  		wp_insert_term('Tag 2', 'tag', array(
  			'slug' => 'tag2'
  		));

  		wp_set_object_terms(1, array(1), 'tag');
		wp_set_object_terms(2, array(2), 'tag');
		wp_set_object_terms(3, array(1, 2), 'tag');

		Indexer::addOrUpdate((object) array(
			'post_type' => 'post',
			'post_date' => '10/24/1988 00:00:00 CST',
			'ID' => 1
		));

		Indexer::addOrUpdate((object) array(
			'post_type' => 'post',
			'post_date' => '10/24/1988 00:00:00 CST',
			'ID' => 2
		));

		Indexer::addOrUpdate((object) array(
			'post_type' => 'post',
			'post_date' => '10/24/1988 00:00:00 CST',
			'ID' => 3
		));

		$this->index->refresh();

		$results = $this->searcher->search(null, 0, 10, array('tag' => 'tag1'));

		$this->assertEquals(2, $results['total']);
		$this->assertEquals(0, count(array_diff(array(1,3), $results['ids'])));
		$this->assertEquals(array('tag' => array('tag2' => 1, 'tag1' => 2)), $results['facets']);

		$results = $this->searcher->search(null, 0, 10, array('tag' => array('tag1')));

		$this->assertEquals(2, $results['total']);
		$this->assertEquals(0, count(array_diff(array(1,3), $results['ids'])));
		$this->assertEquals(array('tag' => array('tag2' => 1, 'tag1' => 2)), $results['facets']);

		$results = $this->searcher->search(null, 0, 10, array('tag' => 'tag2'));

		$this->assertEquals(2, $results['total']);
		$this->assertEquals(0, count(array_diff(array(2,3), $results['ids'])));
		$this->assertEquals(array('tag' => array('tag2' => 2, 'tag1' => 1)), $results['facets']);

		$results = $this->searcher->search(null, 0, 10, array('tag' => array('tag2')));

		$this->assertEquals(2, $results['total']);
		$this->assertEquals(0, count(array_diff(array(2,3), $results['ids'])));
		$this->assertEquals(array('tag' => array('tag2' => 2, 'tag1' => 1)), $results['facets']);

		$results = $this->searcher->search(null, 0, 10, array('tag' => array('tag1', 'tag2')));

		$this->assertEquals(1, $results['total']);
		$this->assertEquals(array(3), $results['ids']);
		$this->assertEquals(array('tag' => array('tag2' => 1, 'tag1' => 1)), $results['facets']);

		$results = $this->searcher->search(null, 0, 10, array('tag' => array( 'and' => array('tag1', 'tag2'))));

		$this->assertEquals(1, $results['total']);
		$this->assertEquals(array(3), $results['ids']);
		$this->assertEquals(array('tag' => array('tag2' => 1, 'tag1' => 1)), $results['facets']);

		$results = $this->searcher->search(null, 0, 10, array('tag' => array('tag1', 'tag3')));

		$this->assertEquals(0, $results['total']);
		$this->assertEquals(array(), $results['ids']);
		$this->assertEquals(array(), $results['facets']);

		$results = $this->searcher->search(null, 0, 10, array('tag' => array( 'and' => array('tag1', 'tag3'))));

		$this->assertEquals(0, $results['total']);
		$this->assertEquals(array(), $results['ids']);
		$this->assertEquals(array(), $results['facets']);

		$results = $this->searcher->search(null, 0, 10, array('tag' => array( 'or' => array('tag1', 'tag2'))));

		$this->assertEquals(3, $results['total']);
		$this->assertEquals(0, count(array_diff(array(1,2, 3), $results['ids'])));
		$this->assertEquals(array('tag' => array('tag2' => 2, 'tag1' => 2)), $results['facets']);

		$results = $this->searcher->search(null, 0, 10, array('tag' => array( 'or' => array('tag1', 'tag3'))));

		$this->assertEquals(2, $results['total']);
		$this->assertEquals(0, count(array_diff(array(1,3), $results['ids'])));
		$this->assertEquals(array('tag' => array('tag2' => 2, 'tag1' => 2)), $results['facets']);
	}

	public function testSearchRangeSegments()
	{
		update_option('numeric', array('field1' => 1));
		update_option('fields', array('field1' => 1));
		update_option('field1_range', '-10,10-20,20-');

		register_post_type('post');

		Indexer::addOrUpdate((object) array(
			'post_type' => 'post',
			'post_date' => '10/24/1988 00:00:00 CST',
			'ID' => 1,
			'field1' => 5
		));

		Indexer::addOrUpdate((object) array(
			'post_type' => 'post',
			'post_date' => '10/24/1988 00:00:00 CST',
			'ID' => 2,
			'field1' =>15
		));

		Indexer::addOrUpdate((object) array(
			'post_type' => 'post',
			'post_date' => '10/24/1988 00:00:00 CST',
			'ID' => 3,
			'field1' =>17
		));

		Indexer::addOrUpdate((object) array(
			'post_type' => 'post',
			'post_date' => '10/24/1988 00:00:00 CST',
			'ID' => 4,
			'field1' => 23
		));

		Indexer::addOrUpdate((object) array(
			'post_type' => 'post',
			'post_date' => '10/24/1988 00:00:00 CST',
			'ID' => 5,
			'field1' => 25
		));

		Indexer::addOrUpdate((object) array(
			'post_type' => 'post',
			'post_date' => '10/24/1988 00:00:00 CST',
			'ID' => 6,
			'field1' => 27
		));

		$this->index->refresh();

		$results = $this->searcher->search(null, 0, 10, array('field1' => '-10'));

		$this->assertEquals(1, $results['total']);
		$this->assertEquals(array(1), $results['ids']);
		$this->assertEquals(array('field1' => array('-10' => 1, '10-20' => 0, '20-' => 0)), $results['facets']);

		$results = $this->searcher->search(null, 0, 10, array('field1' => '10-20'));

		$this->assertEquals(2, $results['total']);
		$this->assertEquals(0, count(array_diff(array(2, 3), $results['ids'])));
		$this->assertEquals(array('field1' => array('-10' => 0, '10-20' => 2, '20-' => 0)), $results['facets']);

		$results = $this->searcher->search(null, 0, 10, array('field1' => array('-10', '10-20')));

		$this->assertEquals(0, $results['total']);
		$this->assertEquals(array(), $results['ids']);
		$this->assertEquals(array('field1' => array('-10' => 0, '10-20' => 0, '20-' => 0)), $results['facets']);

		$results = $this->searcher->search(null, 0, 10, array('field1' => array( 'and' => array('-10', '10-20'))));

		$this->assertEquals(0, $results['total']);
		$this->assertEquals(array(), $results['ids']);
		$this->assertEquals(array('field1' => array('-10' => 0, '10-20' => 0, '20-' => 0)), $results['facets']);

		$results = $this->searcher->search(null, 0, 10, array('field1' => array( 'or' => array('-10', '10-20'))));

		$this->assertEquals(3, $results['total']);
		$this->assertEquals(0, count(array_diff(array(1, 2, 3), $results['ids'])));
		$this->assertEquals(array('field1' => array('-10' => 1, '10-20' => 2, '20-' => 3)), $results['facets']);

		$results = $this->searcher->search(null, 0, 10, array('field1' => '20-'));

		$this->assertEquals(3, $results['total']);
		$this->assertEquals(0, count(array_diff(array(4, 5, 6), $results['ids'])));
		$this->assertEquals(array('field1' => array('-10' => 0, '10-20' => 0, '20-' => 3)), $results['facets']);

		$results = $this->searcher->search(null, 0, 10, array('field1' => array('-10', '20-')));

		$this->assertEquals(0, $results['total']);
		$this->assertEquals(array(), $results['ids']);
		$this->assertEquals(array('field1' => array('-10' => 0, '10-20' => 0, '20-' => 0)), $results['facets']);

		$results = $this->searcher->search(null, 0, 10, array('field1' => array( 'and' => array('-10', '20-'))));

		$this->assertEquals(0, $results['total']);
		$this->assertEquals(array(), $results['ids']);
		$this->assertEquals(array('field1' => array('-10' => 0, '10-20' => 0, '20-' => 0)), $results['facets']);

		$results = $this->searcher->search(null, 0, 10, array('field1' => array('10-20', '20-')));

		$this->assertEquals(0, $results['total']);
		$this->assertEquals(array(), $results['ids']);
		$this->assertEquals(array('field1' => array('-10' => 0, '10-20' => 0, '20-' => 0)), $results['facets']);

		$results = $this->searcher->search(null, 0, 10, array('field1' => array( 'and' => array('10-20', '20-'))));

		$this->assertEquals(0, $results['total']);
		$this->assertEquals(array(), $results['ids']);
		$this->assertEquals(array('field1' => array('-10' => 0, '10-20' => 0, '20-' => 0)), $results['facets']);

		$results = $this->searcher->search(null, 0, 10, array('field1' => array( 'or' => array('10-20', '20-'))));

		$this->assertEquals(5, $results['total']);
		$this->assertEquals(0, count(array_diff(array(4, 5, 6, 2, 3), $results['ids'])));
		$this->assertEquals(array('field1' => array('-10' => 1, '10-20' => 2, '20-' => 3)), $results['facets']);

		$results = $this->searcher->search(null, 0, 10, array('field1' => array( 'or' => array('-10', '20-'))));

		$this->assertEquals(4, $results['total']);
		$this->assertEquals(0, count(array_diff(array(4, 5, 1, 6), $results['ids'])));
		$this->assertEquals(array('field1' => array('-10' => 1, '10-20' => 2, '20-' => 3)), $results['facets']);

		$results = $this->searcher->search(null, 0, 10, array('field1' => array( 'or' => array('-10', '10-20', '20-'))));

		$this->assertEquals(6, $results['total']);
		$this->assertEquals(0, count(array_diff(array(4, 5, 1, 6, 2, 3), $results['ids'])));
		$this->assertEquals(array('field1' => array('-10' => 1, '10-20' => 2, '20-' => 3)), $results['facets']);
	}

	public function testSearchRangeLadderUp()
	{
		update_option('numeric', array('field1' => 1));
		update_option('fields', array('field1' => 1));
		update_option('field1_range', '0-,10-,20-');

		register_post_type('post');

		Indexer::addOrUpdate((object) array(
			'post_type' => 'post',
			'post_date' => '10/24/1988 00:00:00 CST',
			'ID' => 1,
			'field1' => 5
		));

		Indexer::addOrUpdate((object) array(
			'post_type' => 'post',
			'post_date' => '10/24/1988 00:00:00 CST',
			'ID' => 2,
			'field1' =>15
		));

		Indexer::addOrUpdate((object) array(
			'post_type' => 'post',
			'post_date' => '10/24/1988 00:00:00 CST',
			'ID' => 3,
			'field1' =>17
		));

		Indexer::addOrUpdate((object) array(
			'post_type' => 'post',
			'post_date' => '10/24/1988 00:00:00 CST',
			'ID' => 4,
			'field1' => 23
		));

		Indexer::addOrUpdate((object) array(
			'post_type' => 'post',
			'post_date' => '10/24/1988 00:00:00 CST',
			'ID' => 5,
			'field1' => 25
		));

		Indexer::addOrUpdate((object) array(
			'post_type' => 'post',
			'post_date' => '10/24/1988 00:00:00 CST',
			'ID' => 6,
			'field1' => 27
		));

		$this->index->refresh();

		$results = $this->searcher->search(null, 0, 10, array('field1' => '0-'));

		$this->assertEquals(6, $results['total']);
		$this->assertEquals(0, count(array_diff(array(4, 5, 1, 6, 2, 3), $results['ids'])));
		$this->assertEquals(array('field1' => array('0-' => 6, '10-' => 5, '20-' => 3)), $results['facets']);

		$results = $this->searcher->search(null, 0, 10, array('field1' => '10-'));

		$this->assertEquals(5, $results['total']);
		$this->assertEquals(0, count(array_diff(array(4, 5, 6, 2, 3), $results['ids'])));
		$this->assertEquals(array('field1' => array('0-' => 5, '10-' => 5, '20-' => 3)), $results['facets']);

		$results = $this->searcher->search(null, 0, 10, array('field1' => '20-'));

		$this->assertEquals(3, $results['total']);
		$this->assertEquals(0, count(array_diff(array(4, 5, 6), $results['ids'])));
		$this->assertEquals(array('field1' => array('0-' => 3, '10-' => 3, '20-' => 3)), $results['facets']);
	}

	public function testSearchRangeLadderDown()
	{
		update_option('numeric', array('field1' => 1));
		update_option('fields', array('field1' => 1));
		update_option('field1_range', '-30,-20,-10');

		register_post_type('post');

		Indexer::addOrUpdate((object) array(
			'post_type' => 'post',
			'post_date' => '10/24/1988 00:00:00 CST',
			'ID' => 1,
			'field1' => 5
		));

		Indexer::addOrUpdate((object) array(
			'post_type' => 'post',
			'post_date' => '10/24/1988 00:00:00 CST',
			'ID' => 2,
			'field1' =>15
		));

		Indexer::addOrUpdate((object) array(
			'post_type' => 'post',
			'post_date' => '10/24/1988 00:00:00 CST',
			'ID' => 3,
			'field1' =>17
		));

		Indexer::addOrUpdate((object) array(
			'post_type' => 'post',
			'post_date' => '10/24/1988 00:00:00 CST',
			'ID' => 4,
			'field1' => 23
		));

		Indexer::addOrUpdate((object) array(
			'post_type' => 'post',
			'post_date' => '10/24/1988 00:00:00 CST',
			'ID' => 5,
			'field1' => 25
		));

		Indexer::addOrUpdate((object) array(
			'post_type' => 'post',
			'post_date' => '10/24/1988 00:00:00 CST',
			'ID' => 6,
			'field1' => 27
		));

		$this->index->refresh();

		$results = $this->searcher->search(null, 0, 10, array('field1' => '-30'));

		$this->assertEquals(6, $results['total']);
		$this->assertEquals(0, count(array_diff(array(4, 5, 1, 6, 2, 3), $results['ids'])));
		$this->assertEquals(array('field1' => array('-10' => 1, '-20' => 3, '-30' => 6)), $results['facets']);

		$results = $this->searcher->search(null, 0, 10, array('field1' => '-20'));

		$this->assertEquals(3, $results['total']);
		$this->assertEquals(0, count(array_diff(array(1, 2, 3), $results['ids'])));
		$this->assertEquals(array('field1' => array('-10' => 1, '-20' => 3, '-30' => 3)), $results['facets']);

		$results = $this->searcher->search(null, 0, 10, array('field1' => '-10'));

		$this->assertEquals(1, $results['total']);
		$this->assertEquals(0, count(array_diff(array(1), $results['ids'])));
		$this->assertEquals(array('field1' => array('-10' => 1, '-20' => 1, '-30' => 1)), $results['facets']);
	}
}
?>