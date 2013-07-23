<?php
namespace elasticsearch;

class ApiTest extends BaseTestCase
{
	public function testOptionDefined()
	{
		update_option('foo', 'bar');

		$this->assertEquals('bar', Config::option('foo'));
	}

	public function testOptionNotDefined()
	{
		$this->assertNull(Config::option('baz'));
	}

	public function testScoreOption()
	{
		update_option('score_type_name', 'bar');

		$this->assertEquals('bar', Config::score('type', 'name'));
	}

	public function testRangesFieldNotFound()
	{
		$this->assertNull(Config::ranges('field'));
	}

	public function testRangesFieldFound()
	{
		update_option('field_range', '-10,10-20,20-');

		$ranges = Config::ranges('field');

		$this->assertCount(3, $ranges);
		
		$this->assertArrayHasKey('-10', $ranges);
		$this->assertCount(1, $ranges['-10']);
		$this->assertArrayHasKey('to', $ranges['-10']);
		$this->assertEquals(10, $ranges['-10']['to']);

		$this->assertArrayHasKey('10-20', $ranges);
		$this->assertCount(2, $ranges['10-20']);
		$this->assertArrayHasKey('to', $ranges['10-20']);
		$this->assertArrayHasKey('from', $ranges['10-20']);
		$this->assertEquals(10, $ranges['10-20']['from']);
		$this->assertEquals(20, $ranges['10-20']['to']);

		$this->assertArrayHasKey('20-', $ranges);
		$this->assertCount(1, $ranges['20-']);
		$this->assertArrayHasKey('from', $ranges['20-']);
		$this->assertEquals(20, $ranges['20-']['from']);
	}

	public function testFieldsDefault()
	{
		$this->assertEquals(array('post_content', 'post_title', 'post_date'), Config::fields());
	}

	public function testFieldsDefined()
	{
		update_option('fields', array('post_content' => 1));

		$this->assertEquals(array('post_content', 'post_date'), Config::fields());
	}

	public function testFieldsFilter()
	{
		add_filter('es_api_fields', function(){
			return array('filtered');
		});

		$this->assertEquals(array('filtered'), Config::fields());
	}

	public function testTaxonomiesDefined()
	{
		update_option('taxonomies', array('tax1' => 1, 'tax2' => 1));

		$this->assertEquals(array('tax1', 'tax2'), Config::taxonomies());
	}

	public function testTaxonomiesDefault()
	{
		register_post_type('post');
		register_taxonomy('tax1', 'post');
		register_taxonomy('tax2', 'post');

		$this->assertEquals(array('tax1', 'tax2'), Config::taxonomies());
	}

	public function testTypesDefined()
	{
		register_post_type('post');
		register_post_type('review');

		$this->assertEquals(array('post', 'review'), Config::types());
	}

	public function testTypesDefault()
	{
		update_option('types', array('post' => 1, 'review' => 1));

		$this->assertEquals(array('post', 'review'), Config::types());
	}
}
?>