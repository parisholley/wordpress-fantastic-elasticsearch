<?php
namespace elasticsearch;

class BaseTestCase extends \PHPUnit_Framework_TestCase{
	protected function setUp()
	{
		$this->reset(TestContext::$options);
		$this->reset(TestContext::$filters);
		$this->reset(TestContext::$taxes);
		$this->reset(TestContext::$types);
	}

	private function reset(&$array){
		foreach($array as $key => $value){
			unset($array[$key]);
		}

		reset($array);
	}
}
?>