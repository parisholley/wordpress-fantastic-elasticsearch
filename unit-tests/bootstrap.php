<?php
namespace elasticsearch{
	error_reporting(E_ALL);

	$loader = require __DIR__.'/../src/bootstrap.php';
	$loader->add(null, __DIR__.'/../src', true);
	$loader->add(null, __DIR__.'/../lib', true);
	$loader->add(null, __DIR__.'/../unit-tests', true);

	class TestContext{
		static $options = array();
		static $filters = array();
		static $taxes = array();
		static $types = array();
		static $terms = array();
		static $termrel = array();
		static $posts = array();
	}
}

namespace {
	class WP_Query{
		public function __construct($args){
			global $wp_query;

			$wp_query = $this;
			$this->args = $args;
			$this->found_posts = 100;
		}
	}

	function wp_insert_post($post){
		$index = count(elasticsearch\TestContext::$posts) + 1;
		$post['ID'] = $index;

		elasticsearch\TestContext::$posts[$index] = (object) $post;
	}

	function get_posts($args){
		return count(elasticsearch\TestContext::$posts) > 0 ? elasticsearch\TestContext::$posts : $args;
	}

	function &get_option($name){
		return elasticsearch\TestContext::$options;
	}

	function get_post_types(){
		return elasticsearch\TestContext::$types;
	}

	function register_post_type($type){
		elasticsearch\TestContext::$types[count(elasticsearch\TestContext::$types)] = $type;
		elasticsearch\TestContext::$taxes[$type] = array();
	}

	function wp_set_object_terms($postid, $termids, $term){
		if(!isset(elasticsearch\TestContext::$termrel[$postid])){
			elasticsearch\TestContext::$termrel[$postid] = array();
		}

		elasticsearch\TestContext::$termrel[$postid][$term] = $termids;
	}

	function wp_insert_term($term, $tax, $args){
		if(!isset(elasticsearch\TestContext::$terms[$tax])){
			elasticsearch\TestContext::$terms[$tax] = array();
		}

		$index = count(elasticsearch\TestContext::$terms[$tax]) + 1;

		elasticsearch\TestContext::$terms[$tax][$index] = (object) array_merge($args, array(
			'name' => $term,
			'ID' => $index
		));
	}

	function wp_get_object_terms($postid, $tax){
		if(isset(elasticsearch\TestContext::$termrel[$postid][$tax])){
			$results = array();

			foreach(elasticsearch\TestContext::$termrel[$postid][$tax] as $termid){
				if(isset(elasticsearch\TestContext::$terms[$tax][$termid])){
					$results[] = elasticsearch\TestContext::$terms[$tax][$termid];
				}
			}

			return $results;
		}

		return array();
	}

	function get_term($id, $tax){
		if(isset(elasticsearch\TestContext::$terms[$tax][$id])){
			return elasticsearch\TestContext::$terms[$tax][$id];
		}		

		return null;
	}

	function get_object_taxonomies($type){
		if(isset(elasticsearch\TestContext::$taxes[$type])){
			return elasticsearch\TestContext::$taxes[$type];
		}

		return array();
	}

	function register_taxonomy($tax, $type){
		$taxes = &elasticsearch\TestContext::$taxes[$type];

		$taxes[count($taxes)] = $tax;
	}

	function update_option($name, $value){
		elasticsearch\TestContext::$options[$name] = $value;
	}

	function add_filter($name, $function){
		elasticsearch\TestContext::$filters[$name] = $function;
	}

	function apply_filters($name, $val){
		if(isset(elasticsearch\TestContext::$filters[$name])){
			$func = elasticsearch\TestContext::$filters[$name];
			return $func($val);
		}

		return $val;
	}
}
?>