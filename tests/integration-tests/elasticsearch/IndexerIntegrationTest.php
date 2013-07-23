<?php
namespace elasticsearch;

class IndexerIntegrationTest extends BaseIntegrationTestCase
{
	public function testAddOrUpdate()
	{
		update_option('fields', array('field1' => 1, 'field2' => 1));

		register_post_type('post');

		Indexer::addOrUpdate((object) array(
			'post_type' => 'post',
			'ID' => 1,
			'field1' => 'value1',
			'field2' => 'value2'
		));

		$this->index->refresh();

		$this->assertEquals(1, $this->index->count(new \Elastica\Query(array(
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

	public function testClear()
	{
		update_option('fields', array('field1' => 1, 'field2' => 1));

		register_post_type('post');

		Indexer::addOrUpdate((object) array(
			'post_type' => 'post',
			'ID' => 1,
			'field1' => 'value1',
			'field2' => 'value2'
		));

		$this->index->refresh();

		$this->assertEquals(1, $this->index->count());

		Indexer::clear();

		$this->index->refresh();

		$this->assertEquals(0, $this->index->count());
	}

	public function testDeleteDontExist()
	{
		update_option('fields', array('field1' => 1, 'field2' => 1));

		register_post_type('post');

		$post = (object) array(
			'post_type' => 'post',
			'ID' => 1,
			'field1' => 'value1',
			'field2' => 'value2'
		);

		Indexer::addOrUpdate($post);

		$this->index->refresh();

		$this->assertEquals(1, $this->index->count(new \Elastica\Query(array(
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

		$post = (object) array(
			'post_type' => 'post',
			'ID' => 2,
			'field1' => 'value1',
			'field2' => 'value2'
		);

		Indexer::delete($post);

		$this->index->refresh();

		$this->assertEquals(1, $this->index->count());
	}

	public function testDelete()
	{
		update_option('fields', array('field1' => 1, 'field2' => 1));

		register_post_type('post');

		$post = (object) array(
			'post_type' => 'post',
			'ID' => 1,
			'field1' => 'value1',
			'field2' => 'value2'
		);

		Indexer::addOrUpdate($post);

		$this->index->refresh();

		$this->assertEquals(1, $this->index->count(new \Elastica\Query(array(
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

		Indexer::delete($post);

		$this->index->refresh();

		$this->assertEquals(0, $this->index->count());
	}

	public function testReindex()
	{
		update_option('fields', array('field1' => 1, 'field2' => 1));

		register_post_type('post');

		wp_insert_post(array(
			'post_type' => 'post',
			'field1' => 'value1',
			'field2' => 'value2'
		));

		Indexer::reindex();

		$this->index->refresh();

		$this->assertEquals(1, $this->index->count(new \Elastica\Query(array(
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

		Indexer::_map();
		Indexer::reindex();

		$this->index->refresh();

		$search = new \Elastica\Search($this->index->getClient());
		$search->addIndex($this->index);

		$results = $search->search(new \Elastica\Query(array(
			'sort' => array('field3' => array('order' => 'asc'))
		)))->getResults();

		$this->assertCount(2, $results);
		$this->assertEquals(1, $results[0]->getId());
		$this->assertEquals(2, $results[1]->getId());

		$results = $search->search(new \Elastica\Query(array(
			'sort' => array('field3' => array('order' => 'desc'))
		)))->getResults();

		$this->assertCount(2, $results);
		$this->assertEquals(2, $results[0]->getId());
		$this->assertEquals(1, $results[1]->getId());
	}

	public function testMapNonNumeric()
	{
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

		Indexer::_map();
		Indexer::reindex();

		$this->index->refresh();

		$search = new \Elastica\Search($this->index->getClient());
		$search->addIndex($this->index);

		$results = $search->search(new \Elastica\Query(array(
			'sort' => array('field5' => array('order' => 'asc'))
		)))->getResults();

		$this->assertCount(2, $results);
		$this->assertEquals(2, $results[0]->getId());
		$this->assertEquals(1, $results[1]->getId());

		$results = $search->search(new \Elastica\Query(array(
			'sort' => array('field5' => array('order' => 'desc'))
		)))->getResults();

		$this->assertCount(2, $results);
		$this->assertEquals(1, $results[0]->getId());
		$this->assertEquals(2, $results[1]->getId());
	}

	public function testMapDate()
	{
		update_option('fields', array('post_date' => 1));

		register_post_type('post');

		wp_insert_post(array(
			'post_type' => 'post',
			'post_date' => '07/30/1989 00:00:00 CST'
		));

		wp_insert_post(array(
			'post_type' => 'post',
			'post_date' => '10/30/1988 00:00:00 CST'
		));

		Indexer::_map();
		Indexer::reindex();

		$this->index->refresh();

		$search = new \Elastica\Search($this->index->getClient());
		$search->addIndex($this->index);

		$results = $search->search(new \Elastica\Query(array(
			'sort' => array('post_date' => array('order' => 'asc'))
		)))->getResults();

		$this->assertCount(2, $results);
		$this->assertEquals(2, $results[0]->getId());
		$this->assertEquals(1, $results[1]->getId());

		$results = $search->search(new \Elastica\Query(array(
			'sort' => array('post_date' => array('order' => 'desc'))
		)))->getResults();

		$this->assertCount(2, $results);
		$this->assertEquals(1, $results[0]->getId());
		$this->assertEquals(2, $results[1]->getId());
	}
}
?>