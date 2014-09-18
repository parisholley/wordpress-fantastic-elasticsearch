<?php
class TagsTest extends BaseTestCase
{
	public function testTagList()
	{
		$this->url('/?tag=tag1');
		$this->assertEquals('tag1 | Vagrant', $this->title());

		$this->byXPath('(//article[1])[@id="post-55"]');
		$this->byXPath('(//article[2])[@id="post-45"]');
		$this->byXPath('(//article[3])[@id="post-37"]');	
		$this->byXPath('(//article[4])[@id="post-32"]');
		$this->byXPath('(//article[5])[@id="post-5"]');
	}

    public function testTagDrilldown()
	{
		$this->url('/?tag=tag1');
		$this->assertEquals('tag1 | Vagrant', $this->title());

		$this->byCssSelector('#facet-post_tag-tag3 a')->click();

		$this->byXPath('(//aside[@id="facet-post_tag-selected"]//li[1])[@id="facet-post_tag-tag3"]');

		$this->byXPath('(//article[1])[@id="post-55"]');
		$this->byXPath('(//article[2])[@id="post-37"]');
		$this->byXPath('(//article[3])[@id="post-32"]');

		$this->byCssSelector('#facet-post_tag-tag7 a')->click();	

		$this->byXPath('(//aside[@id="facet-post_tag-selected"]//li[2])[@id="facet-post_tag-tag7"]');

		$this->byXPath('(//article[1])[@id="post-32"]');	

		$this->byCssSelector('#facet-post_tag-tag3 a')->click();

		$this->byXPath('(//aside[@id="facet-post_tag-selected"]//li[1])[@id="facet-post_tag-tag7"]');

		$this->byXPath('(//article[1])[@id="post-45"]');
		$this->byXPath('(//article[2])[@id="post-32"]');
	}
}
?>