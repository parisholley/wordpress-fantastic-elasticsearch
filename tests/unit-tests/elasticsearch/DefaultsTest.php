<?php
/**
 * Created by PhpStorm.
 * User: petereussen
 * Date: 18/12/14
 * Time: 15:49
 */

namespace elasticsearch;


class DefaultsTest  extends BaseTestCase
{
	public function testFieldsFilter()
	{
		add_filter('elasticsearch_default_fields', function( $fields ) { return array('blurb'); });

		$this->assertEquals(array('blurb'),Defaults::fields());
	}

	public function testMetaFieldsFilter()
	{
		add_filter('elasticsearch_default_meta_fields', function( $fields ) { return array('blurb'); });

		$this->assertEquals(array('blurb'),Defaults::meta_fields());

	}
}