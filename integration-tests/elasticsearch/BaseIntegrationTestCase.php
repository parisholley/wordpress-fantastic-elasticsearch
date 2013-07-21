<?php
namespace elasticsearch;

class BaseIntegrationTestCase extends BaseTestCase
{
	protected function setUp()
	{
		parent::setUp();

		update_option('server_url', 'http://127.0.0.1:9200/');
		update_option('server_index', 'travisci');	

		$this->index = Indexer::index(true);
		$this->index->create(array(), true);
		
		// make sure index is available before continuing
        Indexer::client(true)->request('_cluster/health/travisci?wait_for_status=yellow', \Elastica\Request::GET);

		$this->assertEquals(0, $this->index->count());
	}
}
?>