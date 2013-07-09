<?php
namespace elasticsearch;

class TemplateTest extends BaseTestCase
{
	public function testFacets()
	{
		global $wp_query;

		$wp_query->facets = array('test');

		$this->assertEquals(array('test'), Template::facets());
	}
}
?>