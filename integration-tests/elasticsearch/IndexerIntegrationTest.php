<?php
namespace elasticsearch;

class IndexerIntegrationTest extends BaseTestCase
{
	public function testAddOrUpdate()
	{
		update_option('server_url', 'http://site:74f7074c9bed64b4b367664f282e7d21@api.searchbox.io/');
		update_option('server_index', 'travisci');
		update_option('fields', array('field1' => 1, 'field2' => 1));

		register_post_type('post');

		$post = (object) array(
			'post_type' => 'post',
			'ID' => 1,
			'field1' => 'value1',
			'field2' => 'value2'
		);

		$index = Api::index(true);

		Indexer::clear();

		$this->assertEquals(0, $index->count());

		Indexer::addOrUpdate($index, $post);

		sleep(1);

		$this->assertEquals(1, $index->count(new \Elastica_Query(array(
			'query' => array(
				'bool' => array(
					'must' => array(
						array( 'match' => array( '_id' => 1 ) ),
						array( 'match' => array( 'field1' => 'value1' ) ),
						array( 'match' => array( 'field2' => 'value2' ) )
					)
				)
			)
		))));
	}

	public function testDelete()
	{
		update_option('server_url', 'http://site:74f7074c9bed64b4b367664f282e7d21@api.searchbox.io/');
		update_option('server_index', 'travisci');
		update_option('fields', array('field1' => 1, 'field2' => 1));

		register_post_type('post');

		$post = (object) array(
			'post_type' => 'post',
			'ID' => 1,
			'field1' => 'value1',
			'field2' => 'value2'
		);

		$index = Api::index(true);

		Indexer::clear();

		$this->assertEquals(0, $index->count());

		Indexer::addOrUpdate($index, $post);

		sleep(1);

		$this->assertEquals(1, $index->count(new \Elastica_Query(array(
			'query' => array(
				'bool' => array(
					'must' => array(
						array( 'match' => array( '_id' => 1 ) ),
						array( 'match' => array( 'field1' => 'value1' ) ),
						array( 'match' => array( 'field2' => 'value2' ) )
					)
				)
			)
		))));

		Indexer::delete($index, $post);

		sleep(1);

		$this->assertEquals(0, $index->count());
	}

	public function testReindex()
	{
		update_option('server_url', 'http://site:74f7074c9bed64b4b367664f282e7d21@api.searchbox.io/');
		update_option('server_index', 'travisci');
		update_option('fields', array('field1' => 1, 'field2' => 1));

		register_post_type('post');

		wp_insert_post(array(
			'post_type' => 'post',
			'field1' => 'value1',
			'field2' => 'value2'
		));

		$index = Api::index(true);

		Indexer::clear();

		$this->assertEquals(0, $index->count());

		Indexer::reindex();

		sleep(1);

		$this->assertEquals(1, $index->count(new \Elastica_Query(array(
			'query' => array(
				'bool' => array(
					'must' => array(
						array( 'match' => array( '_id' => 1 ) ),
						array( 'match' => array( 'field1' => 'value1' ) ),
						array( 'match' => array( 'field2' => 'value2' ) )
					)
				)
			)
		))));
	}

	public function testMapNumeric()
	{
		update_option('server_url', 'http://site:74f7074c9bed64b4b367664f282e7d21@api.searchbox.io/');
		update_option('server_index', 'travisci');
		update_option('fields', array('field3' => 1));
		update_option('numeric', array('field3' => 1));

		register_post_type('post');

		wp_insert_post(array(
			'post_type' => 'post',
			'field3' => '7'
		));

		wp_insert_post(array(
			'post_type' => 'post',
			'field3' => '30'
		));

		$index = Api::index(false);

		Indexer::clear();

		$this->assertEquals(0, $index->count());

		Indexer::reindex();

		sleep(1);

		$search = new \Elastica_Search($index->getClient());
		$search->addIndex($index);

		$results = $search->search(new \Elastica_Query(array(
			'sort' => array('field3' => array('order' => 'asc'))
		)))->getResults();

		$this->assertCount(2, $results);
		$this->assertEquals(1, $results[0]->getId());
		$this->assertEquals(2, $results[1]->getId());

		$results = $search->search(new \Elastica_Query(array(
			'sort' => array('field3' => array('order' => 'desc'))
		)))->getResults();

		$this->assertCount(2, $results);
		$this->assertEquals(2, $results[0]->getId());
		$this->assertEquals(1, $results[1]->getId());
	}

	public function testMapNonNumeric()
	{
		update_option('server_url', 'http://site:74f7074c9bed64b4b367664f282e7d21@api.searchbox.io/');
		update_option('server_index', 'travisci');
		update_option('fields', array('field5' => 1));

		register_post_type('post');

		wp_insert_post(array(
			'post_type' => 'post',
			'field5' => '7'
		));

		wp_insert_post(array(
			'post_type' => 'post',
			'field5' => '30'
		));

		$index = Api::index(false);

		Indexer::clear();

		$this->assertEquals(0, $index->count());

		Indexer::reindex();

		sleep(1);

		$search = new \Elastica_Search($index->getClient());
		$search->addIndex($index);

		$results = $search->search(new \Elastica_Query(array(
			'sort' => array('field5' => array('order' => 'asc'))
		)))->getResults();

		$this->assertCount(2, $results);
		$this->assertEquals(2, $results[0]->getId());
		$this->assertEquals(1, $results[1]->getId());

		$results = $search->search(new \Elastica_Query(array(
			'sort' => array('field5' => array('order' => 'desc'))
		)))->getResults();

		$this->assertCount(2, $results);
		$this->assertEquals(1, $results[0]->getId());
		$this->assertEquals(2, $results[1]->getId());
	}
}
?>