<?php
/*
Plugin Name: Fantastic ElasticSearch
Plugin URI: http://wordpress.org/extend/plugins/fantastic-elasticsearch/
Description: Improve wordpress search performance and accuracy by leveraging an ElasticSearch server.
Version: 2.0.2
Author: Paris Holley
Author URI: http://www.linkedin.com/in/parisholley
Author Email: mail@parisholley.com
License:

	The MIT License (MIT)

	Copyright (c) 2013 Paris Holley <mail@parisholley.com>

	Permission is hereby granted, free of charge, to any person obtaining a copy
	of this software and associated documentation files (the "Software"), to deal
	in the Software without restriction, including without limitation the rights
	to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
	copies of the Software, and to permit persons to whom the Software is
	furnished to do so, subject to the following conditions:

	The above copyright notice and this permission notice shall be included in
	all copies or substantial portions of the Software.

	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
	AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
	LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
	OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
	THE SOFTWARE.
*/

namespace elasticsearch;

if(!defined('NHP_OPTIONS_URL')){
	define('NHP_OPTIONS_URL', plugins_url('/wp/lib/nhp/options/', __FILE__));
}

if(!class_exists('NHP_Options')){
	require_once( dirname( __FILE__ ) . '/wp/lib/nhp/options/options.php' );
}

require 'src/bootstrap.php';

require 'wp/theme/search.php';
require 'wp/theme/category.php';
require 'wp/admin/hooks.php';

add_action( 'admin_enqueue_scripts', function() {
	wp_register_style( 'custom_wp_admin_css', plugins_url('wp/css/admin.css', __FILE__) );
	wp_enqueue_style( 'custom_wp_admin_css' );
});

add_action('init', function(){
	$args = array();

	$args['share_icons']['twitter'] = array(
		'link' => 'http://twitter.com/parisholley',
		'title' => 'Folow me on Twitter', 
		'img' => NHP_OPTIONS_URL.'img/glyphicons/glyphicons_322_twitter.png'
	);

	$args['share_icons']['linked_in'] = array(
		'link' => 'http://www.linkedin.com/in/parisholley',
		'title' => 'Find me on LinkedIn', 
		'img' => NHP_OPTIONS_URL.'img/glyphicons/glyphicons_337_linked_in.png'
	);

	$args['opt_name'] = 'elasticsearch';
	$args['menu_title'] = 'ElasticSearch';
	$args['page_title'] = 'ElasticSearch';
	$args['page_slug'] = 'elastic_search';
	$args['show_import_export'] = false;
	$args['page_position'] = 10241988;
	$args['dev_mode'] = false;
	$args['menu_icon'] = plugins_url('/wp/images/menu.png', __FILE__);
	$args['page_icon'] = 'elasticsearch-icon';

	$sections = array();

	require('wp/admin/sections/wordpress-integration.php');
	require('wp/admin/sections/server-settings.php');
	require('wp/admin/sections/content-indexing.php');
	require('wp/admin/sections/field-mapping.php');
	require('wp/admin/sections/results-scoring.php');
	require('wp/admin/sections/manage-index.php');

	global $NHP_Options;

    	$tabs = array();

	$sections = Config::apply_filters("nhp_options_section_setup", $sections);
	$args = Config::apply_filters("nhp_options_args_setup", $args);

	$NHP_Options = new \NHP_Options($sections, $args, $tabs);
}, 10241988);
?>
