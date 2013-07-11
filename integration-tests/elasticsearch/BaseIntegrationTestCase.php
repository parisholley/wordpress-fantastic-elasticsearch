<?php
namespace elasticsearch;

class BaseIntegrationTestCase extends BaseTestCase
{
	protected function setUp()
	{
		parent::setUp();

		update_option('server_url', 'http://127.0.0.1:9200/');
		update_option('server_index', 'travisci');	

		$this->index = Api::index(false);
		$this->index->create(array(), true);

		// see http://elasticsearch-users.115913.n3.nabble.com/How-to-wait-for-a-CreateIndexRequest-to-really-finish-using-java-TransportClient-td4027828.html
		$this->index->addAlias('foobar');

		$this->assertEquals(0, $this->index->count());
	}
}
?>