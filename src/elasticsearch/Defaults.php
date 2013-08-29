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
		return array('post_content', 'post_title', 'post_type');
	}

	/**
	* Returns any post types currently defined in wordpress
	*
	* @return string[] post type names
	**/
	static function types(){
		$types = get_post_types();

		$available = array();

		foreach($types as $type){
			$tobject = get_post_type_object($type);

			if(!$tobject->exclude_from_search && $type != 'attachment'){
				$available[] = $type;
			}
		}

		return $available;
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
