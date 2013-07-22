<?php
namespace elasticsearch;

/**
* Returns a set of default values that are sufficient for indexing wordpress if the user does not set any values.
*
* @license http://opensource.org/licenses/MIT
* @author Paris Holley <mail@parisholley.com>
* @version 2.0.0
**/
class Defaults{
	/**
	* Useful field names that wordpress provides out the box
	*
	* @return string[] field names
	**/
	static function fields(){
		return array('post_date', 'post_content', 'post_title');
	}

	/**
	* Returns any post types currently defined in wordpress
	*
	* @return string[] post type names
	**/
	static function types(){
		return get_post_types();
	}

	/**
	* Returns any taxonomies registered for the provided post types
	*
	* @return string[] taxonomy slugs
	**/
	static function taxonomies($types){
		$taxes = array();

		foreach($types as $type){
			$taxes = array_merge($taxes, get_object_taxonomies($type));
		}

		return array_unique($taxes); 
	}
}
?>
