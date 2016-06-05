<?php
namespace elasticsearch;

/**
 * Returns a set of default values that are sufficient for indexing wordpress if the user does not set any values.
 *
 * @license http://opensource.org/licenses/MIT
 * @author Paris Holley <mail@parisholley.com>
 * @version 4.0.1
 **/
class Defaults
{
	/**
	 * Useful field names that wordpress provides out the box
	 *
	 * @return string[] field names
	 **/
	static function fields()
	{
		return array('post_content', 'post_title', 'post_type');
	}

	/**
	 * Returns any post types currently defined in wordpress
	 *
	 * @return string[] post type names
	 **/
	static function types()
	{
		$types = get_post_types();

		$available = array();

		foreach ($types as $type) {
			$tobject = get_post_type_object($type);

			if (!$tobject->exclude_from_search && $type != 'attachment') {
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
	static function taxonomies($types)
	{
		$taxes = array();

		foreach ($types as $type) {
			$taxes = array_merge($taxes, get_object_taxonomies($type));
		}

		return array_unique($taxes);
	}

	/**
	 * Returns all customfields registered for any post type.
	 * Copied method meta_form() from admin/includes/templates.php as inline method ... damn those dirty wordpress suckers!!!
	 * @return string[] meta keys sorted
	 **/
	static function meta_fields()
	{

		global $wpdb;
		$keys = $wpdb->get_col("SELECT meta_key
                            FROM $wpdb->postmeta
                            GROUP BY meta_key
                            HAVING meta_key NOT LIKE '\_%'
                            ORDER BY meta_key");
		if ($keys) {
			natcasesort($keys);
		} else {
			$keys = array();
		}
		return $keys;
	}
}

?>
