<?php
namespace elasticsearch;

class BaseTestCase extends \PHPUnit_Framework_TestCase{
	protected function setUp()
	{
		global $wp_query;

		$this->reset(TestContext::$options);
		$this->reset(TestContext::$filters);
		$this->reset(TestContext::$taxes);
		$this->reset(TestContext::$types);

		$wp_query = (object) array();
	}

	private function reset(&$array){
		foreach($array as $key => $value){
			unset($array[$key]);
		}

		reset($array);
	}
}
?>