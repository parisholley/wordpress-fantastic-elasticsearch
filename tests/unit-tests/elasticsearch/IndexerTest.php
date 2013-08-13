<?php
namespace elasticsearch;

class IndexerTest extends BaseTestCase
{
	public function testClientWriteDefault()
	{
		$client = Indexer::_client(true);
		$this->assertEquals(300, $client->getConfig('timeout'));
	}

	public function testClientWriteConfig()
	{
		update_option('server_timeout_write', 30);

		$client = Indexer::_client(true);
		$this->assertEquals(30, $client->getConfig('timeout'));
	}

	public function testClientReadDefault()
	{
		$client = Indexer::_client(false);
		$this->assertEquals(1, $client->getConfig('timeout'));
	}

	public function testClientReadConfig()
	{
		update_option('server_timeout_read', 100);

		$client = Indexer::_client(false);
		$this->assertEquals(100, $client->getConfig('timeout'));
	}

	/**
     * @expectedException \Elastica\Exception\InvalidException
     */
	public function testIndexNotDefined()
	{
		$client = Indexer::_index(false);
	}

	public function testIndexDefined()
	{
		update_option('server_index', 'index_name');

		$index = Indexer::_index(false);
		$this->assertEquals('index_name', $index->getName());
	}

	public function testPerPage()
	{
		$this->assertEquals(10, Indexer::per_page());
	}

	public function testPerPageFiltered()
	{
		add_filter('elasticsearch_indexer_per_page', function(){
			return 100;
		});

		$this->assertEquals(100, Indexer::per_page());
	}

	public function testGetPosts()
	{
		register_post_type('post');

		$this->assertEquals(array(
			'posts_per_page' => 10,
			'post_type' => Config::types(),
			'paged' => 2,
			'post_status' => 'publish'
		), Indexer::get_posts(2));
	}

	public function testGetPostsFiltered()
	{
		add_filter('elasticsearch_indexer_get_posts', function(){
			return array('wee');
		});

		$this->assertEquals(array('wee'), Indexer::get_posts(2));
	}

	public function testGetCount()
	{
		global $wp_query;

		register_post_type('post');

		$this->assertEquals(100, Indexer::get_count());
		$this->assertEquals(array('post'), $wp_query->args['post_type']);
		$this->assertEquals('publish', $wp_query->args['post_status']);
	}

	public function testBuildDocumentFiltered()
	{
		add_filter('elasticsearch_indexer_build_document', function(){
			return array('wee');
		});
		
		$document = Indexer::_build_document(array());
		$this->assertEquals(array('wee'), $document);
	}

	public function testBuildDocument()
	{
		update_option('fields', array('field1' => 1, 'post_date' => 1));
		
		register_post_type('post');
		register_taxonomy('tax1', 'post');
		register_taxonomy('tax2', 'post');
		
		wp_insert_term('Term 1', 'tax1', array(
			'slug' => 'term1'
		));

		wp_insert_term('Term 2', 'tax1', array(
			'slug' => 'term2',
			'parent' => 1
		));

		wp_insert_term('Term 3', 'tax1', array(
			'slug' => 'term3',
			'parent' => 2
		));

		wp_insert_term('Term 4', 'tax2', array(
			'slug' => 'term4'
		));

		wp_insert_term('Term 5', 'tax2', array(
			'slug' => 'term5'
		));

		wp_insert_term('Term 6', 'tax2', array(
			'slug' => 'term6',
			'parent' => 1
		));


		wp_set_object_terms(2, array(3), 'tax1');
		wp_set_object_terms(2, array(2, 3), 'tax2');

		$post = (object) array(
			'field1' => 'value1',
			'ID' => 2,
			'post_date' => '10/24/1988 00:00:00 CST',
			'post_type' => 'post'
		);

		$tz = date_default_timezone_get();
		
		date_default_timezone_set('America/Chicago');
		
		$document = Indexer::_build_document($post);
		
		date_default_timezone_set($tz);

		$this->assertEquals(array(
			'field1' => 'value1',
			'post_date' => '1988-10-24T01:00:00-05:00',
			'tax1' => array('term3', 'term2', 'term1'),
			'tax2' => array('term5', 'term6', 'term4'),
			'tax1_name' => array('Term 3', 'Term 2', 'Term 1'),
			'tax2_name' => array('Term 5', 'Term 6', 'Term 4'),
			'blog_id' => 1
		), $document);
	}
}
?>