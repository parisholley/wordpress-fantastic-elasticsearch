<?php
abstract class BaseTestCase extends PHPUnit_Extensions_Selenium2TestCase
{
	protected static $indexed = false;

	protected function setUp()
	{
		$this->setBrowser('phantomjs');
		$this->setBrowserUrl('http://localhost:8080/wordpress/');
	}

	public function setUpPage(){
		$this->timeouts()->implicitWait(3000);
		
		if(!self::$indexed){
			$this->url('/wp-admin/');

			if('Vagrant › Log In' == $this->title()){
				$this->byId('user_login')->click();
				$this->keys('admin');

				sleep(1); // some reason too fast?

				$this->byId('user_pass')->click();
				$this->keys('vagrant');

				$this->byId('wp-submit')->click();
			}

			$this->waitForTitle('Dashboard ‹ Vagrant — WordPress');
			
			$this->url('http://localhost:8080/wordpress/wp-admin/admin.php?page=elastic_search&tab=index');
			$this->assertEquals('ElasticSearch ‹ Vagrant — WordPress', $this->title());

			sleep(1); // some reason too fast?

			$this->byId('wipedata')->click();

			$this->byId('nhp-opts-save');

			sleep(1);
			
			$this->byId('reindex')->click();

			$this->byCssSelector(".complete");

			self::$indexed = true;

			// give index time to refresh, would rather call refresh on index directly, too lazy :)
			sleep(5);
		}
	}
	
	protected function waitForTitle($title){
		for($i = 0; $i < 5; $i++){
			if($this->title() == $title){
				return;
			}

			sleep(1);
		}

		throw new Exception("Could not verify title '$title'");
	}
}
?>