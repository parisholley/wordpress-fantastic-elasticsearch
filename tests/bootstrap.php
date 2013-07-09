<?php
namespace elasticsearch;

error_reporting(E_ALL);

$loader = require __DIR__.'/../src/bootstrap.php';
$loader->add(null, __DIR__.'/../src', true);
$loader->add(null, __DIR__.'/../lib', true);
$loader->add(null, __DIR__.'/../tests', true);

class TestContext{
	static $options = array();
	static $filters = array();
	static $taxes = array();
	static $types = array();
}

function &get_option($name){
	return TestContext::$options;
}

function get_post_types(){
	return TestContext::$types;
}

function register_post_type($type){
	TestContext::$types[count(TestContext::$types)] = $type;
	TestContext::$taxes[$type] = array();
}

function get_object_taxonomies($type){
	if(isset(TestContext::$taxes[$type])){
		return TestContext::$taxes[$type];
	}

	return array();
}

function register_taxonomy($tax, $type){
	$taxes = &TestContext::$taxes[$type];

	$taxes[count($taxes)] = $tax;
}

function update_option($name, $value){
	TestContext::$options[$name] = $value;
}

function add_filter($name, $function){
	TestContext::$filters[$name] = $function;
}

function apply_filters($name, $val){
	if(isset(TestContext::$filters[$name])){
		$func = TestContext::$filters[$name];
		return $func($val);
	}

	return $val;
}
?>