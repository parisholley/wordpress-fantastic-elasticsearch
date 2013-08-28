<?php
namespace elasticsearch;

abstract class BaseTestCase extends \PHPUnit_Framework_TestCase{
	protected function setUp()
	{
		global $wp_query, $blog_id, $wpdb;

		$this->reset(TestContext::$options);
		$this->reset(TestContext::$filters);
		$this->reset(TestContext::$taxes);
		$this->reset(TestContext::$types);
		$this->reset(TestContext::$posts);
		$this->reset(TestContext::$terms);
		$this->reset(TestContext::$termrel);
		$this->reset(TestContext::$all_meta_keys);

		$wp_query = new \stdClass();
    $wpdb = new \wpdb();
		$blog_id = 1;

		$_GET = array();
	}

	private function reset(&$array){
		foreach($array as $key => $value){
			unset($array[$key]);
		}

		reset($array);
	}
}
?>