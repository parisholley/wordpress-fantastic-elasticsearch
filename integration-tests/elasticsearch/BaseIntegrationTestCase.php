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
		
		sleep(.5);

		$this->assertEquals(0, $this->index->count());
	}
}
?>