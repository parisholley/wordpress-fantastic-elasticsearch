<?php
class CategoryTest extends BaseTestCase
{
	public function testMultipleCategories()
	{
		$this->url('/?cat=2,3');
		$this->assertEquals('Parent Category I | Vagrant', $this->title());

		$this->byId('post-57');
		$this->byId('post-45');
		$this->byId('post-41');
		$this->byId('post-39');
		$this->byId('post-32');
		$this->byId('post-5');
	}

	public function testDefaultCategory()
	{
		$this->url('/?cat=2');
		$this->assertEquals('Parent Category I | Vagrant', $this->title());

		$this->byId('post-57');
		$this->byId('post-45');
		$this->byId('post-5');
		$this->byId('post-39');
	}

	public function testTags()
	{
		$this->url('/?cat=2&post_tag[and][]=tag1');
		$this->assertEquals('Parent Category I | Vagrant', $this->title());

		$this->byId('post-45');
		$this->byId('post-5');
	}

	public function testCategoryFaceting()
	{
		$this->url('/?cat=2');
		$this->assertEquals('Parent Category I | Vagrant', $this->title());

		$this->byXPath('(//aside[@id="facet-category-available"]//li[1])[@id="facet-category-child-category-i"]');
		$this->byXPath('(//aside[@id="facet-category-available"]//li[2])[@id="facet-category-child-category-ii"]');
		$this->byXPath('(//aside[@id="facet-category-available"]//li[3])[@id="facet-category-grandchild-category-i"]');
		$this->byXPath('(//aside[@id="facet-category-available"]//li[4])[@id="facet-category-parent-category-ii"]');

		$this->byCssSelector('#facet-category-child-category-ii a')->click();

		$this->byXPath('(//aside[@id="facet-category-selected"]//li[1])[@id="facet-category-child-category-ii"]');

		$this->byId('post-45');
		$this->byId('post-39');
	}

	public function testTagsFacetingMixed()
	{
		$this->url('/?cat=2');
		$this->assertEquals('Parent Category I | Vagrant', $this->title());

		$this->byXPath('(//aside[@id="facet-post_tag-available"]//li[1])[@id="facet-post_tag-tag1"]');
		$this->byXPath('(//aside[@id="facet-post_tag-available"]//li[2])[@id="facet-post_tag-tag2"]');
		$this->byXPath('(//aside[@id="facet-post_tag-available"]//li[3])[@id="facet-post_tag-tag5"]');

		$this->byCssSelector('#facet-post_tag-tag5 a')->click();

		$this->byXPath('(//aside[@id="facet-post_tag-selected"]//li[1])[@id="facet-post_tag-tag5"]');

		$this->byId('post-57');
		$this->byId('post-45');
		$this->byId('post-5');

		$this->byXPath('(//aside[@id="facet-post_tag-available"]//li[1])[@id="facet-post_tag-tag1"]');

		$this->byCssSelector('#facet-post_tag-tag1 a')->click();

		$this->byXPath('(//aside[@id="facet-post_tag-selected"]//li[1])[@id="facet-post_tag-tag1"]');
		$this->byXPath('(//aside[@id="facet-post_tag-selected"]//li[2])[@id="facet-post_tag-tag5"]');

		$this->byId('post-45');
		$this->byId('post-5');

		$this->byCssSelector('#facet-post_tag-tag1 a')->click();

		$this->byXPath('(//aside[@id="facet-post_tag-selected"]//li[1])[@id="facet-post_tag-tag5"]');

		$this->byId('post-57');
		$this->byId('post-45');
		$this->byId('post-5');

		$this->byXPath('(//aside[@id="facet-post_tag-available"]//li[1])[@id="facet-post_tag-tag1"]');
	}

	public function testTagsFacetingLimitedResults()
	{
		$this->url('/?cat=1');
		$this->assertEquals('Uncategorized | Vagrant', $this->title());

		$this->byXPath('(//aside[@id="facet-post_tag-available"]//li[1])[@id="facet-post_tag-tag2"]');
		$this->byXPath('(//aside[@id="facet-post_tag-available"]//li[2])[@id="facet-post_tag-tag5"]');
		$this->byXPath('(//aside[@id="facet-post_tag-available"]//li[3])[@id="facet-post_tag-tag7"]');

		$this->byCssSelector('#facet-post_tag-tag5 a')->click();

		$this->byXPath('(//aside[@id="facet-post_tag-selected"]//li[1])[@id="facet-post_tag-tag5"]');

		$this->byId('post-43');
		$this->byId('post-50');
	}
}
?>