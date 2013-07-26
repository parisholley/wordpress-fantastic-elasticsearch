<?php
class CategoryTest extends BaseTestCase
{
	public function testDefaultCategory()
	{
		$this->url('/?cat=2');
		$this->assertEquals('Parent Category I | Vagrant', $this->title());

		$this->assertEquals('post-57', $this->byXPath('//article[1]')->attribute('id'));
		$this->assertEquals('post-45', $this->byXPath('//article[2]')->attribute('id'));
		$this->assertEquals('post-39', $this->byXPath('//article[3]')->attribute('id'));
		$this->assertEquals('post-5', $this->byXPath('//article[4]')->attribute('id'));
	}

	public function testTags()
	{
		$this->url('/?cat=2&post_tag[and][]=tag1');
		$this->assertEquals('Parent Category I | Vagrant', $this->title());

		$this->assertEquals('post-45', $this->byXPath('//article[1]')->attribute('id'));
		$this->assertEquals('post-5', $this->byXPath('//article[2]')->attribute('id'));
	}
}
?>