<?php
namespace elasticsearch;

class Template{
	public static function facets(){
		global $wp_query;

		return $wp_query->facets;
	}
}
?>