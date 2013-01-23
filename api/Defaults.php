<?php
namespace elasticsearch;

class Defaults{
	static function get_posts_args(){
		$args = apply_filters('es_get_posts_args', array(
			'posts_per_page' => 9999,
			'post_type' => array('post')
		));

		return $args;
	}

	static function fields(){
		return array('post_date', 'post_content', 'post_title');
	}

	static function types(){
		$args = self::get_posts_args();

		return $args['post_type'];
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