<?php
namespace elasticsearch;

/**
 * This class provides a convenient way of accessing configuration values set through the plugin's admin interface.
 *
 * @license http://opensource.org/licenses/MIT
 * @author Paris Holley <mail@parisholley.com>
 * @version 4.0.1
 **/
class Config
{
	static $options = null;

	/**
	 * Retrieve a specific option from the wordpress database for this plugin. Note: This is cached once per request.
	 *
	 * @param string $name The name of the option
	 *
	 * @return object
	 **/
	static function option($name, $default = null)
	{
		if (self::$options == null) {
			self::$options = get_option('elasticsearch');
		}

		return self::apply_filters('config_option', isset(self::$options[$name]) ? self::$options[$name] : $default, $name);
	}

	/**
	 * Set a specific option within the FES plugin
	 *
	 * @param string $name The name of the option
	 * @param string $value The value of the option
	 *
	 * @return object
	 **/
	static function set($name, $value)
	{
		$options = get_option('elasticsearch') ?: [];
		$options[$name] = $value;

		update_option('elasticsearch', $options);
	}

	/**
	 * Identifies facets that were defined by user but do not follow standard format.
	 *
	 * @return array An array of strings that represent mapped fields
	 **/
	static function customFacets()
	{
		$custom = [];

		foreach (self::facets() as $field) {
			if (!in_array($field, self::fields()) && !in_array($field, self::taxonomies())) {
				$custom[] = $field;
			}
		}

		return self::apply_filters('config_custom_facets', $custom);
	}

	/**
	 * The score given to a data point that determines the impact on search results. May return null if the setttings have not been saved.
	 *
	 * @param string $type The type of wordpress object that is being scored (tax|field)
	 * @param string $name The slug and/or logical name of that type
	 *
	 * @return integer
	 **/
	static function score($type, $name)
	{
		return self::apply_filters('config_score', self::option("score_{$type}_{$name}"), $type, $name);
	}

	/**
	 * The numeric ranges that have been defined for a certain field. Example of output:
	 * <code>
	 *    array(
	 *        '-10' => array(
	 *            'to' => 10
	 *        ),
	 *        '10-20' => array(
	 *            'from' => 10,
	 *            'to' => 20
	 *        )
	 *    )
	 * </code>
	 *
	 * @param string $field The field name to lookup
	 *
	 * @return array An associative array where the keys represent a slug and values are used for configuration.
	 **/
	static function ranges($field)
	{
		$config = self::option($field . '_range');

		$val = null;

		if ($config) {
			$ranges = array();

			foreach (explode(',', $config) as $range) {
				$ends = explode('-', $range);

				$tmp = array();

				if (is_numeric($ends[0])) {
					$tmp['from'] = $ends[0];
				}

				if (is_numeric($ends[1])) {
					$tmp['to'] = $ends[1];
				}

				$ranges[$ends[0] . '-' . $ends[1]] = $tmp;
			}

			$val = $ranges;
		}

		return self::apply_filters('config_ranges', $val, $field);
	}

	/**
	 * Behaves exactly like the wordpress apply_filters method except it prefixes every filter with a convention used by this plugin (ie: 'es_').
	 **/
	static function apply_filters()
	{
		$args = func_get_args();
		$args[0] = 'elasticsearch_' . $args[0];

		return call_user_func_array('apply_filters', $args);
	}

	/**
	 * A list of a fields that are included when indexing data.
	 *
	 * @return string[] field names
	 **/
	static function fields()
	{
		$fieldnames = Defaults::fields();

		$fields = self::option('fields');

		if (is_array($fields)) {
			$fieldnames = array_keys($fields);
		}

		// this should always exist so we have a default to sort on
		$fieldnames[] = 'post_date';

		return self::apply_filters('config_fields', $fieldnames);
	}

	/**
	 * A list of data points that are used for faceting.
	 *
	 * @return string[] field and/or association names
	 **/
	static function facets()
	{
		$facets = self::taxonomies();

		if (in_array('post_type', self::fields())) {
			$facets[] = 'post_type';
		}

		return self::apply_filters('config_facets', $facets);
	}

	/**
	 * A list of wordpress post types that are used for indexing.
	 *
	 * @return string[] post type slugs
	 **/
	static function types()
	{
		$types = self::option('types');

		$val = Defaults::types();

		if ($types) {
			$val = array_keys($types);
		}

		return self::apply_filters('config_types', $val);
	}

	/**
	 * A list of taxonomies that are used for indexing.
	 *
	 * @return string[] taxonomy slugs
	 **/
	static function taxonomies()
	{
		$taxes = self::option('taxonomies');

		$val = null;

		if ($taxes) {
			$val = array_keys($taxes);
		}

		if ($val == null) {
			$val = Defaults::taxonomies(self::types());
		}

		return self::apply_filters('config_taxonomies', $val);
	}

	/**
	 * A list of custom fields that are used for indexing.
	 *
	 * @return string[] meta keys custom field names
	 **/
	static function meta_fields()
	{
		$keys = self::option('meta_fields');

		$val = null;

		if ($keys) {
			$val = array_keys($keys);
		}

		if ($val == null) {
			$val = array();
		}

		return self::apply_filters('config_meta_fields', $val);
	}
}

?>
