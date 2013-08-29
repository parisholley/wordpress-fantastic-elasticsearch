<?php
namespace elasticsearch;

abstract class BaseTestCase extends \PHPUnit_Framework_TestCase{
	protected function setUp()
	{
		global $wp_query, $blog_id;

		$this->reset(Config::$options);
		$this->reset(TestContext::$filters);
		$this->reset(TestContext::$taxes);
		$this->reset(TestContext::$types);
		$this->reset(TestContext::$posts);
		$this->reset(TestContext::$terms);
		$this->reset(TestContext::$termrel);

		$wp_query = new \stdClass();
		$blog_id = 1;

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