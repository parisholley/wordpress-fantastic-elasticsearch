<?php
namespace elasticsearch;

class BaseIntegrationTestCase extends BaseTestCase
{
	protected function setUp()
	{
		parent::setUp();

		update_option('server_url', 'http://127.0.0.1:9200/');
		update_option('server_index', 'travisci');	

		$this->index = Indexer::_index(true);
		$this->index->create(array(), true);
		
		// make sure index is available before continuing
        Indexer::_client(true)->request('_cluster/health/travisci?wait_for_status=yellow', \Elastica\Request::GET);

		$this->assertEquals(0, $this->index->count());
	}

	public function isVersion($version){
		$info = Indexer::_client(true)->request('/')->getData();
		
		return $info['version']['number'] == $version;
	}

    /**
     * Match if 2 arrays have the same keys and/or values regardless of order
     * @param $a
     * @param $b
     * @return bool
     */
    public function  arraysAreSimilar($a, $b) {
    // if the indexes don't match, return immediately
    if (count(array_diff($a, $b)) > 0) {
        return false;
    }
    // we have identical indexes, and no unequal values
    return true;
    }
}
?>