<?php
namespace elasticsearch;

class SearcherTest extends BaseTestCase
{
	public function testFacetNoFacets()
	{
		$facets = array();

		$searcher = new Searcher();

		$musts = array();
		$filters = array();

		$searcher->_facet('facet', $facets, 'term', $musts, $filters);

		$this->assertCount(0, $musts);
		$this->assertCount(0, $filters);
	}

	public function testFacetSingle()
	{
		$facets = array(
			'facet' => 'value'
		);

		$searcher = new Searcher();

		$musts = array();
		$filters = array();

		$searcher->_facet('facet', $facets, 'term', $musts, $filters);

		$this->assertCount(1, $musts);
		$this->assertCount(0, $filters);

		$this->assertEquals(array(
			array(
				'term' => array(
					'facet' => 'value'
				)
			)
		), $musts);
	}

	public function testFacetSingleTransalte()
	{
		$facets = array(
			'facet' => 'value'
		);

		$searcher = new Searcher();

		$musts = array();
		$filters = array();
		$translate = array(
			'value' => 'foobar'
		);

		$searcher->_facet('facet', $facets, 'term', $musts, $filters, $translate);

		$this->assertCount(1, $musts);
		$this->assertCount(0, $filters);

		$this->assertEquals(array(
			array(
				'term' => array(
					'facet' => 'foobar'
				)
			)
		), $musts);
	}

	public function testFacetMultipleNumKey()
	{
		$facets = array(
			'facet' => array('value1', 'value2')
		);

		$searcher = new Searcher();

		$musts = array();
		$filters = array();

		$searcher->_facet('facet', $facets, 'term', $musts, $filters);

		$this->assertCount(2, $musts);
		$this->assertCount(0, $filters);

		$this->assertEquals(array(
			array(
				'term' => array(
					'facet' => 'value1'
				)
			),
			array(
				'term' => array(
					'facet' => 'value2'
				)
			)
		), $musts);
	}

	public function testFacetMultipleStringKeyArray()
	{
		$facets = array(
			'facet' => array(
				'and' => array('value1', 'value2'),
				'or' => array('value3', 'value4')
			)
		);

		$searcher = new Searcher();

		$musts = array();
		$filters = array();

		$searcher->_facet('facet', $facets, 'term', $musts, $filters);

		$this->assertCount(2, $musts);
		$this->assertCount(2, $filters);

		$this->assertEquals(array(
			array(
				'term' => array(
					'facet' => 'value1'
				)
			),
			array(
				'term' => array(
					'facet' => 'value2'
				)
			)
		), $musts);

		$this->assertEquals(array(
			array(
				'term' => array(
					'facet' => 'value3'
				)
			),
			array(
				'term' => array(
					'facet' => 'value4'
				)
			)
		), $filters);
	}

		public function testFacetMultipleStringKeySingle()
	{
		$facets = array(
			'facet' => array(
				'and' => 'value1',
				'or' => 'value2'
			)
		);

		$searcher = new Searcher();

		$musts = array();
		$filters = array();

		$searcher->_facet('facet', $facets, 'term', $musts, $filters);

		$this->assertCount(1, $musts);
		$this->assertCount(1, $filters);

		$this->assertEquals(array(
			array(
				'term' => array(
					'facet' => 'value1'
				)
			)
		), $musts);

		$this->assertEquals(array(
			array(
				'term' => array(
					'facet' => 'value2'
				)
			)
		), $filters);
	}
}
?>