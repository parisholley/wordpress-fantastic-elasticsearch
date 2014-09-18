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

		$this->byId('post-37');
		$this->byId('post-41');
		$this->byId('post-43');
		$this->byId('post-5');
		$this->byId('post-57');
		$this->byId('post-45');
	}

	public function testExactPhraseWrong()
	{
		$this->url('/');
		$this->assertEquals('Vagrant | Just another WordPress site', $this->title());

		$this->byName('s')->click();
		$this->keys('"ipsum lorem"');

		$this->byId('searchsubmit')->click();
		$this->assertEquals('“ipsum lorem” | Search Results | Vagrant', $this->title());

		// not sure how ES is coming up with this order
		$this->assertEquals('Nothing Found', $this->byCssSelector('.entry-title')->text());
	}

	public function testExactPhraseRight()
	{
		$this->url('/');
		$this->assertEquals('Vagrant | Just another WordPress site', $this->title());

		$this->byName('s')->click();
		$this->keys('"lorem ipsum"');

		$this->byId('searchsubmit')->click();
		$this->assertEquals('“lorem ipsum” | Search Results | Vagrant', $this->title());

		$this->byId('post-39');
		$this->byId('post-32');
		$this->byId('post-43');
		$this->byId('post-55');
		$this->byId('post-5');
		$this->byId('post-57');
		$this->byId('post-45');
		$this->byId('post-35');
	}
}
?>