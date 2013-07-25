<?php
class WebTest extends PHPUnit_Extensions_Selenium2TestCase
{
	protected static $indexed;

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
			$this->byId('reindex')->click();

			$this->byCssSelector(".complete");

			self::$indexed = true;
		}
	}

	public function testInvalidTag()
	{
		$this->url('/');
		$this->assertEquals('Vagrant | Just another WordPress site', $this->title());

		$this->byName('s')->click();
		$this->keys('tag10');

		$this->byId('searchsubmit')->click();
		$this->assertEquals('tag10 | Search Results | Vagrant', $this->title());

		$this->assertEquals('Nothing Found', $this->byCssSelector('.entry-title')->text());
	}

    public function testPartialTag()
	{
		$this->url('/');
		$this->assertEquals('Vagrant | Just another WordPress site', $this->title());

		$this->byName('s')->click();
		$this->keys('tag');

		$this->byId('searchsubmit')->click();
		$this->assertEquals('tag | Search Results | Vagrant', $this->title());

		$this->assertEquals('Nothing Found', $this->byCssSelector('.entry-title')->text());
	}

	public function testValidTag()
	{
		$this->url('/');
		$this->assertEquals('Vagrant | Just another WordPress site', $this->title());

		$this->byName('s')->click();
		$this->keys('tag2');

		$this->byId('searchsubmit')->click();
		$this->assertEquals('tag2 | Search Results | Vagrant', $this->title());

		$this->assertEquals('post-57', $this->byXPath('//article[1]')->attribute('id'));
		$this->assertEquals('post-45', $this->byXPath('//article[2]')->attribute('id'));
		$this->assertEquals('post-43', $this->byXPath('//article[3]')->attribute('id'));
		$this->assertEquals('post-41', $this->byXPath('//article[4]')->attribute('id'));
		$this->assertEquals('post-37', $this->byXPath('//article[5]')->attribute('id'));
		$this->assertEquals('post-5', $this->byXPath('//article[6]')->attribute('id'));
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