<?php
namespace elasticsearch;

class ApiTest extends BaseTestCase
{
	public function testTaxonomiesFilter()
	{
		add_filter('elasticsearch_config_taxonomies', function($value){
			return array('bar');
		}, 10, 1);

		$this->assertEquals(array('bar'), Config::taxonomies());
	}

	public function testTypesFilter()
	{
		add_filter('elasticsearch_config_types', function($value){
			return array('bar');
		}, 10, 1);

		$this->assertEquals(array('bar'), Config::types());
	}

	public function testFacetsFilter()
	{
		add_filter('elasticsearch_config_facets', function($value){
			return array('bar');
		}, 10, 1);

		$this->assertEquals(array('bar'), Config::facets());
	}

	public function testOptionFilter()
	{
		add_filter('elasticsearch_config_option', function($value, $name){
			if($name != 'foo'){
				throw new Exception('args not same');
			}

			return 'bar';
		}, 10, 2);

		$this->assertEquals('bar', Config::option('foo'));
	}

	public function testScoreFilter()
	{
		add_filter('elasticsearch_config_score', function($value, $type, $name){
			if($type != 'field' || $name != 'foo'){
				throw new Exception('args not same');
			}

			return 0;
		}, 10, 3);

		$this->assertEquals(0, Config::score('field', 'foo'));
	}

	public function testRangesFilter()
	{
		add_filter('elasticsearch_config_ranges', function($value, $field){
			if($field != 'field'){
				throw new Exception('args not same');
			}

			return array('wee');
		}, 10, 2);

		$this->assertEquals(array('wee'), Config::ranges('field'));
	}

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
		add_filter('elasticsearch_config_fields', function(){
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

	public function testTypesUndefined()
	{
		register_post_type('post');
		register_post_type('review', array(
			'exclude_from_search' => true
		));

		$this->assertEquals(array('post'), Config::types());
	}

	public function testTypesDefined()
	{
		update_option('types', array('post' => 1, 'review' => 1));

		$this->assertEquals(array('post', 'review'), Config::types());
	}
}
?>