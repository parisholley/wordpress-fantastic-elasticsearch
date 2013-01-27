<?php
namespace elasticsearch;

class Template{
	public function facets(){
		global $wp_query;

		return $wp_query->facets;
	}
}
?>