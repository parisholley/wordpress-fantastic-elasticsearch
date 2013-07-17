<?php
namespace elasticsearch;

class ApiTest extends BaseTestCase
{
	public function testOptionDefined()
	{
		update_option('foo', 'bar');

		$this->assertEquals('bar', Api::option('foo'));
	}

	public function testOptionNotDefined()
	{
		$this->assertNull(Api::option('baz'));
	}

	public function testScoreOption()
	{
		update_option('score_type_name', 'bar');

		$this->assertEquals('bar', Api::score('type', 'name'));
	}

	public function testRangesFieldNotFound()
	{
		$this->assertNull(Api::ranges('field'));
	}

	public function testRangesFieldFound()
	{
		update_option('field_range', '-10,10-20,20-');

		$ranges = Api::ranges('field');

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

	public function testClientWriteDefault()
	{
		$client = Api::client(true);
		$this->assertEquals(300, $client->getConfig('timeout'));
	}

	public function testClientWriteConfig()
	{
		update_option('server_timeout_write', 30);

		$client = Api::client(true);
		$this->assertEquals(30, $client->getConfig('timeout'));
	}

	public function testClientReadDefault()
	{
		$client = Api::client(false);
		$this->assertEquals(1, $client->getConfig('timeout'));
	}

	public function testClientReadConfig()
	{
		update_option('server_timeout_read', 100);

		$client = Api::client(false);
		$this->assertEquals(100, $client->getConfig('timeout'));
	}

	/**
     * @expectedException \Elastica\Exception\InvalidException
     */
	public function testIndexNotDefined()
	{
		$client = Api::index(false);
	}

	public function testIndexDefined()
	{
		update_option('server_index', 'index_name');

		$index = Api::index(false);
		$this->assertEquals('index_name', $index->getName());
	}

	public function testFieldsDefault()
	{
		$this->assertEquals(array('post_date', 'post_content', 'post_title'), Api::fields());
	}

	public function testFieldsDefined()
	{
		update_option('fields', array('post_date' => 1, 'post_content' => 1));

		$this->assertEquals(array('post_date', 'post_content'), Api::fields());
	}

	public function testFieldsFilter()
	{
		add_filter('es_api_fields', function(){
			return array('filtered');
		});

		$this->assertEquals(array('filtered'), Api::fields());
	}

	public function testTaxonomiesDefined()
	{
		update_option('taxonomies', array('tax1' => 1, 'tax2' => 1));

		$this->assertEquals(array('tax1', 'tax2'), Api::taxonomies());
	}

	public function testTaxonomiesDefault()
	{
		register_post_type('post');
		register_taxonomy('tax1', 'post');
		register_taxonomy('tax2', 'post');

		$this->assertEquals(array('tax1', 'tax2'), Api::taxonomies());
	}

	public function testTypesDefined()
	{
		register_post_type('post');
		register_post_type('review');

		$this->assertEquals(array('post', 'review'), Api::types());
	}

	public function testTypesDefault()
	{
		update_option('types', array('post' => 1, 'review' => 1));

		$this->assertEquals(array('post', 'review'), Api::types());
	}
}
?>