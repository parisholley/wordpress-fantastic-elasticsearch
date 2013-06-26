<?php
namespace elasticsearch;

class Defaults{
	static function fields(){
		return array('post_date', 'post_content', 'post_title', 'post_author');
	}

	static function types(){
		return get_post_types();
	}

	static function taxonomies($types){
		$taxes = array();

		foreach($types as $type){
			$taxes = array_merge($taxes, get_object_taxonomies($type));
		}

		return array_unique($taxes); 
	}
}
?>