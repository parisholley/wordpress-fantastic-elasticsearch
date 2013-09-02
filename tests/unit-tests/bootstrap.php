<?php
namespace elasticsearch{
	error_reporting(E_ALL | E_STRICT);

	$loader = require __DIR__.'/../../src/bootstrap.php';
	$loader->add(null, __DIR__.'/../unit-tests', true);
	$loader->add(null, __DIR__.'/../integration-tests', true);

	class TestContext{
		static $options = array();
		static $option = array();
		static $filters = array();
		static $taxes = array();
		static $types = array();
		static $terms = array();
		static $termrel = array();
		static $posts = array();
		static $actions = array();
		static $is = array();
		static $all_meta_keys = array();
		static $post_meta = array();
	}
}

namespace {
	class WP_Widget{

	}
	
	class WP_Query{
		public $is_main_query;
		public $query_vars = array();
		public $found_posts;

		public function __construct($args=array()){
			global $wp_query;

			$wp_query = $this;
			$this->args = $args;
			$this->found_posts = 100;
		}

		public function is_main_query(){
			return $this->is_main_query;
		}
	}

  // ock for cstom field tests
  class wpdb {
    // name of the post meta db table
    public $postmeta = 'wp_postmeta';

    public function get_col($query){
      return isset(elasticsearch\TestContext::$all_meta_keys) ? elasticsearch\TestContext::$all_meta_keys : null;
    }
  }


	function __($val){
		return $val;
	}

	function register_setting(){

	}

	function add_settings_section(){

	}

	function is_tag(){
		return true;
	}

	function is_archive(){
		return true;
	}

	function is_category(){
		return true;
	}

	function checked(){
		return false;
	}

	function add_settings_field($arg1, $arg2, $callback, $arg4, $arg5, $field){
		return call_user_func_array($callback, array($field));
	}

	function wp_parse_args( $args, $defaults = '' ) {
		if ( is_object( $args ) )
			$r = get_object_vars( $args );
		elseif ( is_array( $args ) )
			$r =& $args;
		else
			wp_parse_str( $args, $r );

		if ( is_array( $defaults ) )
			return array_merge( $defaults, $r );
		return $r;
	}

	function get_transient($val){
		return $val;
	}

	function delete_transient($val){
		return $val;
	}

	function wp_insert_post($post){
		$index = count(elasticsearch\TestContext::$posts) + 1;
		$post['ID'] = $index;

		elasticsearch\TestContext::$posts[$index] = (object) $post;
	}

	function get_posts($args){
		return count(elasticsearch\TestContext::$posts) > 0 ? elasticsearch\TestContext::$posts : $args;
	}

	function get_post_type_object($name){
		foreach(elasticsearch\TestContext::$types as $type){
			if($type->name == $name){
				return $type;
			}
		}

		return null;
	}

	function &get_option($name){
		return elasticsearch\TestContext::$option[$name];
	}

	function get_post_types(){
		$names = array();

		foreach(elasticsearch\TestContext::$types as $type){
			$names[] = $type->name;
		}

		return $names;
	}

	function register_post_type($type, $args = array()){
		$args = array_merge(array(
			'exclude_from_search' => false,
			'name' => $type
		), $args);

		elasticsearch\TestContext::$types[count(elasticsearch\TestContext::$types)] = (object) $args;
		elasticsearch\TestContext::$taxes[$type] = array();
	}

	function wp_set_object_terms($postid, $termids, $term){
		if(!isset(elasticsearch\TestContext::$termrel[$postid])){
			elasticsearch\TestContext::$termrel[$postid] = array();
		}

		elasticsearch\TestContext::$termrel[$postid][$term] = $termids;
	}

	function wp_insert_term($term, $tax, $args = array()){
		if(!isset(elasticsearch\TestContext::$terms[$tax])){
			elasticsearch\TestContext::$terms[$tax] = array();
		}

		$index = count(elasticsearch\TestContext::$terms[$tax]) + 1;

		elasticsearch\TestContext::$terms[$tax][$index] = (object) array_merge($args, array(
			'name' => $term,
			'ID' => $index,
			'term_id' => $index
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

	function get_category($id){
		return get_term($id, 'category');
	}

	function get_category_by_slug($slug){
		foreach(elasticsearch\TestContext::$terms['category'] as $cat){
			if($cat->slug == $slug){
				return $cat;
			}
		}

		return null;
	}

	function get_term($id, $tax){
		if(isset(elasticsearch\TestContext::$terms[$tax][$id])){
			return elasticsearch\TestContext::$terms[$tax][$id];
		}		

		return null;
	}

	function get_terms($tax){
		if(isset(elasticsearch\TestContext::$terms[$tax])){
			return elasticsearch\TestContext::$terms[$tax];
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
		elasticsearch\Config::$options[$name] = $value;
	}

	function add_filter($name, $function, $order = 1, $args = 1){
		elasticsearch\TestContext::$filters[$name] = $function;
	}

	function apply_filters($name){
		if(isset(elasticsearch\TestContext::$filters[$name])){
			$func = elasticsearch\TestContext::$filters[$name];

			$args = func_get_args();
			array_shift($args); // remove $name

			return call_user_func_array($func, $args);
		}

		return func_get_arg(1);
	}

	function add_action($action, $callback){
		elasticsearch\TestContext::$actions[$action][] = $callback;
	}

	function plugins_url($arg){

	}

	function get_post($id){
		return isset(elasticsearch\TestContext::$posts[$id]) ? elasticsearch\TestContext::$posts[$id] : null;
	}

  // todo: maybe add keys nested by post in TestContext
  function get_post_custom_keys( $post_id = 0 ) {
    return isset(elasticsearch\TestContext::$all_meta_keys) ? elasticsearch\TestContext::$all_meta_keys : null;
  }

  function add_post_meta($postid, $key, $value){
    if(!isset(elasticsearch\TestContext::$post_meta[$postid])){
      elasticsearch\TestContext::$post_meta[$postid] = array();
    }

    elasticsearch\TestContext::$post_meta[$postid][$key] = $value;
	}

  function get_post_meta($postid, $key, $args){
    if( isset(elasticsearch\TestContext::$post_meta[$postid]) &&
        isset(elasticsearch\TestContext::$post_meta[$postid][$key])) {
      return elasticsearch\TestContext::$post_meta[$postid][$key];
    }
	}

	function add_meta_keys($keys){
    elasticsearch\TestContext::$all_meta_keys = $keys;
	}

	function trailingslashit($arg){

	}

  function add_menu_page(){

	}

	function add_submenu_page(){

	}

	function wp_register_style(){

	}

	function wp_enqueue_style(){

	}

	function wp_enqueue_script(){

	}

	function wp_localize_script(){

	}

	function get_categories(){
		return array();
	}

	function esc_attr($attr){
		return $attr;
	}

	function do_action($name){
		if(isset(elasticsearch\TestContext::$actions[$name])){
			foreach(elasticsearch\TestContext::$actions[$name] as $action){
				$args = func_get_args();
				array_shift($args); // remove $name

				if(is_array($action)){
					call_user_func_array($action, $args);
				}
			}
		}
	}

	function is_search(){
		return isset(elasticsearch\TestContext::$is['is_search']) ? elasticsearch\TestContext::$is['is_search'] : false;
	}

	function is_admin(){
		return isset(elasticsearch\TestContext::$is['is_admin']) ? elasticsearch\TestContext::$is['is_admin'] : false;
	}

	function is_tax(){
		return isset(elasticsearch\TestContext::$is['is_tax']) ? elasticsearch\TestContext::$is['is_tax'] : false;
	}

	require __DIR__ . '/../../elasticsearch.php';
}
?>