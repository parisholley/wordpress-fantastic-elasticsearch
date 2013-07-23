<?php
class WebTest extends PHPUnit_Extensions_Selenium2TestCase
{
	protected function setUp()
	{
		$this->setBrowser('phantomjs');
		$this->setBrowserUrl('http://www.example.com/');
	}
 
	public function testInvalidTag()
	{
		$this->url('http://localhost:8080/wordpress/');
		$this->assertEquals('Vagrant | Just another WordPress site', $this->title());

		$this->byName('s')->click();
		$this->keys('tag10');

		$this->byId('searchsubmit')->click();
		$this->assertEquals('tag10 | Search Results | Vagrant', $this->title());

		$this->assertEquals('Nothing Found', $this->byCssSelector('.entry-title')->text());
	}

	public function testPartialTag()
	{
		$this->url('http://localhost:8080/wordpress/');
		$this->assertEquals('Vagrant | Just another WordPress site', $this->title());

		$this->byName('s')->click();
		$this->keys('tag');

		$this->byId('searchsubmit')->click();
		$this->assertEquals('tag | Search Results | Vagrant', $this->title());

		$this->assertEquals('Nothing Found', $this->byCssSelector('.entry-title')->text());
	}

	public function testValidTag()
	{
		$this->url('http://localhost:8080/wordpress/');
		$this->assertEquals('Vagrant | Just another WordPress site', $this->title());

		$this->byName('s')->click();
		$this->keys('tag2');

		$this->byId('searchsubmit')->click();
		$this->assertEquals('tag2 | Search Results | Vagrant', $this->title());

		$this->assertEquals('post-56', $this->byXPath('//article[1]')->attribute('id'));
		$this->assertEquals('post-44', $this->byXPath('//article[2]')->attribute('id'));
		$this->assertEquals('post-42', $this->byXPath('//article[3]')->attribute('id'));
		$this->assertEquals('post-40', $this->byXPath('//article[4]')->attribute('id'));
		$this->assertEquals('post-36', $this->byXPath('//article[5]')->attribute('id'));
		$this->assertEquals('post-4', $this->byXPath('//article[6]')->attribute('id'));
	}
}
?>