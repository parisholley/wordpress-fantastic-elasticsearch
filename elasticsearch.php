<?php
/*
Plugin Name: Fantastic ElasticSearch
Plugin URI: http://wordpress.org/extend/plugins/fantastic-elasticsearch/
Description: Improve wordpress search performance and accuracy by leveraging an ElasticSearch server.
Version: 1.1.6
Author: Paris Holley
Author URI: http://www.linkedin.com/in/parisholley
Author Email: mail@parisholley.com
License:

  Copyright 2013 Paris Holley (mail@parisholley.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as 
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
  
*/

namespace elasticsearch;

if(!class_exists('NHP_Options')){
	require_once( dirname( __FILE__ ) . '/lib/nhp/options/options.php' );
}

spl_autoload_register(function($class){
	$path = str_replace('elasticsearch', '', $class);
	$path = str_replace(array('_', "\\"), DIRECTORY_SEPARATOR, $path);

	if (file_exists(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . $path . '.php')) {
		require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . $path . '.php');
	}

	if (file_exists(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'api' . DIRECTORY_SEPARATOR . $path . '.php')) {
		require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'api' . DIRECTORY_SEPARATOR . $path . '.php');
	}
});

require 'theme/search.php';
require 'theme/category.php';
require 'admin/hooks.php';

add_action( 'admin_enqueue_scripts', function() {
	wp_register_style( 'custom_wp_admin_css', plugins_url('/css/admin.css', __FILE__) );
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
	$args['menu_icon'] = plugins_url('/images/menu.png', __FILE__);
	$args['page_icon'] = 'elasticsearch-icon';

	$sections = array();

	require('admin/sections/wordpress-integration.php');
	require('admin/sections/server-settings.php');
	require('admin/sections/content-indexing.php');
	require('admin/sections/results-scoring.php');
	require('admin/sections/manage-index.php');

	global $NHP_Options;

	$NHP_Options = new \NHP_Options($sections, $args, $tabs);
}, 10241988);
?>
