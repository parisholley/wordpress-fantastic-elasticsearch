<?php
namespace elasticsearch;

abstract class BaseTestCase extends \PHPUnit_Framework_TestCase{
	protected function setUp()
	{
		global $wp_query, $blog_id, $wpdb;

		$this->reset(Config::$options);
		$this->reset(TestContext::$filters);
		$this->reset(TestContext::$taxes);
		$this->reset(TestContext::$types);
		$this->reset(TestContext::$posts);
		$this->reset(TestContext::$terms);
		$this->reset(TestContext::$termrel);
		$this->reset(TestContext::$all_meta_keys);
		$this->reset(TestContext::$post_meta);

		$wp_query = new \stdClass();
		$blog_id = 1;
    // used for mocking meta field db calls
    $wpdb = new \wpdb();

		$_GET = array();
	}

	private function reset(&$array){
		if(is_array($array)){
			foreach($array as $key => $value){
				unset($array[$key]);
			}

			reset($array);
		}
	}
}
?>