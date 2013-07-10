<?php
namespace elasticsearch;

class IndexerTest extends BaseTestCase
{
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
			'post_type' => Api::types(),
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
		
		$document = Indexer::build_document(array());
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

		$document = Indexer::build_document($post);

		$this->assertEquals(array(
			'field1' => 'value1',
			'post_date' => '1988-10-24T01:00:00-05:00',
			'tax1' => array('term3', 'term2', 'term1'),
			'tax2' => array('term5', 'term6', 'term4')
		), $document);
	}
}
?>