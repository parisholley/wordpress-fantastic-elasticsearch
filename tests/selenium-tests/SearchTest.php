<?php
class SearchTest extends BaseTestCase
{
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

		// not sure how ES is coming up with this order
		$this->assertEquals('post-37', $this->byXPath('//article[1]')->attribute('id'));
		$this->assertEquals('post-41', $this->byXPath('//article[2]')->attribute('id'));
		$this->assertEquals('post-43', $this->byXPath('//article[3]')->attribute('id'));
		$this->assertEquals('post-5', $this->byXPath('//article[4]')->attribute('id'));
		$this->assertEquals('post-57', $this->byXPath('//article[5]')->attribute('id'));
		$this->assertEquals('post-45', $this->byXPath('//article[6]')->attribute('id'));
	}
}
?>