<?php
class TagsTest extends BaseTestCase
{
	public function testTagList()
	{
		$this->url('/?tag=tag1');
		$this->assertEquals('tag1 | Vagrant', $this->title());

		$this->byId('post-55');
		$this->byId('post-45');
		$this->byId('post-37');
		$this->byId('post-32');
		$this->byId('post-5');
	}

    public function testTagDrilldown()
	{
		$this->url('/?tag=tag1');
		$this->assertEquals('tag1 | Vagrant', $this->title());

		$this->byCssSelector('#facet-post_tag-tag3 a')->click();

		$this->byXPath('(//aside[@id="facet-post_tag-selected"]//li[1])[@id="facet-post_tag-tag3"]');

		$this->byId('post-55');
		$this->byId('post-37');
		$this->byId('post-32');

		$this->byCssSelector('#facet-post_tag-tag7 a')->click();	

		$this->byXPath('(//aside[@id="facet-post_tag-selected"]//li[2])[@id="facet-post_tag-tag7"]');

		$this->byId('post-32');

		$this->byCssSelector('#facet-post_tag-tag3 a')->click();

		$this->byXPath('(//aside[@id="facet-post_tag-selected"]//li[1])[@id="facet-post_tag-tag7"]');

		$this->byId('post-45');
		$this->byId('post-32');
	}
}
?>