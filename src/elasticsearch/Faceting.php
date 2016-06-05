<?php
namespace elasticsearch;

/**
 * This class provides numerous helper methods for working with facet information returned from elastic search results.
 *
 * @license http://opensource.org/licenses/MIT
 * @author Paris Holley <mail@parisholley.com>
 * @version 4.0.1
 **/
class Faceting
{
	/**
	 * A convenient method that aggregates the results of all the other methods in the class. Example of output:
	 *
	 * <code>
	 *    array(
	 *        // the keys are names of fields and/or taxonomies
	 *        'taxonomy' => array(
	 *            'available' => array(
	 *                'taxonomy1'    => array(
	 *                    'count'    => 10,
	 *                    'slug'    => 'taxonomy1',
	 *                    'name'    => 'Taxonomy One',
	 *                    'font'    => 24
	 *                )
	 *            ),
	 *            'selected' => array(
	 *                'taxonomy2'    => array(
	 *                    'slug'    => 'taxonomy2',
	 *                    'name'    => 'Taxonomy Two'
	 *                )
	 *            ),
	 *            'total' => 10
	 *        ),
	 *        'rating' => array(
	 *            'available' => array(
	 *                '10-20' => array(
	 *                    'count'    => 4,
	 *                    'slug'    => '10-20',
	 *                    'to'    => 20,
	 *                    'from'    => 10
	 *                )
	 *            ),
	 *            'total' => 4
	 *        )
	 *    )
	 * </code>
	 *
	 * @param string $minFont The minimum font size to use for display in a tag cloud (defaults to : 12)
	 * @param string $maxFont The maximum font size to use for display in a tag cloud (defaults to : 24)
	 *
	 * @return array An associative array where the keys represent the data point with a list of selected and/or available options.
	 **/
	static function all($minFont = 12, $maxFont = 24)
	{
		$options = array();

		foreach (Config::taxonomies() as $tax) {
			$options[$tax] = self::taxonomy($tax);
		}

		$numeric = Config::option('numeric');

		$fields = array_merge(Config::fields(), Config::meta_fields());

		foreach ($fields as $field) {
			if (isset($numeric[$field])) {
				$options[$field] = self::range($field);
			}

			if ($field == 'post_type') {
				$options['post_type'] = self::types(Config::types());
			}
		}

		foreach (Config::customFacets() as $field) {
			$options[$field] = self::custom($field);
		}

		foreach ($options as $name => &$field) {
			if (isset($field['available'])) {
				foreach ($field['available'] as &$available) {
					$available['font'] = self::cloud($field['available'], $available, $minFont, $maxFont);
				}
			}
		}

		return $options;
	}

	/**
	 * Analyse query parameters for range slugs and determine which facets are selected vs. which are available for the given field. Example of output:
	 *
	 * <code>
	 *    array(
	 *        'available' => array(
	 *            '10-20' => array(
	 *                'count'    => 4,
	 *                'slug'    => '10-20',
	 *                'to'    => 20,
	 *                'from'    => 10
	 *            )
	 *        ),
	 *        'selected' => array(
	 *            '-20' => array(
	 *                'slug'    => '-20',
	 *                'to'    => 20
	 *            )
	 *        ),
	 *        'total' => 4
	 *    )
	 *    </code>
	 *
	 * @param string $field The field to determine range facet information about
	 *
	 * @return array An associative array based on example provided
	 **/
	static function range($field)
	{
		return self::_buildFacetResult($field, Config::ranges($field), function ($slug, $range, $facet) {
			$split = explode('-', $slug);

			return array(
				'slug' => $slug,
				'count' => $facet[$slug],
				'to' => $split[1],
				'from' => $split[0]
			);
		});
	}

	/**
	 * Analyse query parameters for taxonomoy slugs and determine which facets are selected vs. which are available for the given field. Example of output:
	 *
	 * <code>
	 *    array(
	 *        'available' => array(
	 *            'taxonomy1' => array(
	 *                'count' => 10,
	 *                'slug'    => 'taxonomy1',
	 *                'name'    => 'Taxonomy One',
	 *                'font'    => 24
	 *            )
	 *        ),
	 *        'selected' => array(
	 *            'taxonomy2'    => array(
	 *                'slug'    => 'taxonomy2',
	 *                'name'    => 'Taxonomy Two'
	 *            )
	 *        ),
	 *        'total' => 10
	 *    )
	 *    </code>
	 *
	 * @param string $field The taxonomy type to retrieve facet information about
	 *
	 * @return array An associative array based on example provided
	 **/
	static function taxonomy($tax)
	{
		return self::_buildFacetResult($tax, get_terms($tax), function ($key, $term) {
			return array(
				'name' => $term->name ?: $term->slug,
				'slug' => $term->slug
			);
		});
	}

	/**
	 * Gather facet information for custom fields that were indexes. Example of output:
	 *
	 * <code>
	 *    array(
	 *        'available' => array(
	 *            'key1' => array(
	 *                'count' => 4,
	 *                'value'    => 'value1'
	 *            )
	 *        ),
	 *        'selected' => array(
	 *            'key2' => array(
	 *                'count' => 6,
	 *                'value'    => 'value2'
	 *            )
	 *        ),
	 *        'total' => 10
	 *    )
	 *    </code>
	 *
	 * @param string $field Field to lookup in faceting data
	 *
	 * @return array An associative array based on example provided
	 **/
	static function custom($field)
	{
		global $wp_query;

		$data = isset($wp_query->facets[$field]) ? $wp_query->facets[$field] : array();

		return self::_buildFacetResult($field, $data, function ($value) use ($field) {
			$return = array(
				'slug' => $value
			);

			return Config::apply_filters('faceting_custom', $return, $field);
		});
	}

	/**
	 * Gather post type facet information for the provided post types. Example of output:
	 *
	 * <code>
	 *    array(
	 *        'available' => array(
	 *            'post' => array(
	 *                'count' => 10,
	 *                'slug'    => 'post',
	 *                'name'    => 'Posts',
	 *                'font'    => 24
	 *            )
	 *        ),
	 *        'selected' => array(
	 *            'custom_post_type'    => array(
	 *                'slug'    => 'cpt',
	 *                'name'    => 'Customs'
	 *            )
	 *        ),
	 *        'total' => 10
	 *    )
	 *    </code>
	 *
	 * @param string $field The post types that were configured for indexing
	 *
	 * @return array An associative array based on example provided
	 **/
	static function types($types)
	{
		return self::_buildFacetResult('post_type', $types, function ($key, $value) {
			$type = get_post_type_object($value);

			return array(
				'name' => $type->label,
				'slug' => $type->name
			);
		});
	}

	/**
	 * Will calculate a font size based on the total number of results for the given item in a collection of items. Example of output:
	 *
	 * <code>
	 *    array(
	 *        'available' => array(
	 *            'taxonomy1' => array(
	 *                'count' => 10,
	 *                'slug'    => 'taxonomy1',
	 *                'name'    => 'Taxonomy One',
	 *                'font'    => 24
	 *            )
	 *        ),
	 *        'selected' => array(
	 *            'taxonomy2' => array(
	 *                'slug'    => 'taxonomy2',
	 *                'name'    => 'Taxonomy Two'
	 *            )
	 *        ),
	 *        'total' => 10
	 *    )
	 * </code>
	 *
	 * @param array $items An array of arrays that contain a key called 'count'
	 * @param array $item An item out of the array that you wish to calculate a font size
	 * @param string $minFont The minimum font size to use for display in a tag cloud (defaults to : 12)
	 * @param string $maxFont The maximum font size to use for display in a tag cloud (defaults to : 24)
	 *
	 * @return integer The calculated font size
	 **/
	static function cloud($items, $item, $min = 12, $max = 24)
	{
		$maxTotal = 1;

		foreach ($items as $itm) {
			if (log($itm['count']) > $maxTotal) {
				$maxTotal = log($itm['count']);
			}
		}

		return floor((log($item['count']) / $maxTotal) * ($max - $min) + $min);
	}

	/**
	 * Modifies the provided URL by appending query parameters for faceted searching.
	 *
	 * @param string $url The URL of the page that supports F.E.S
	 * @param string $type The data point you wish to enable faceting for (ie: a field name or taxonomy name)
	 * @param string $value The value/slug that was provided by another method call in this class
	 * @param string $operation Whether the facet should query using 'and' or 'or' (defaults to and)
	 *
	 * @return string The URL modified to support faceting
	 **/
	static function urlAdd($url, $type, $value, $operation = 'and')
	{
		$filter = $_GET;

		if (!isset($filter['es'])) {
			$filter['es'] = array();
		}

		$es = &$filter['es'];

		$op = $operation;

		if (isset($es[$type])) {
			$op = array_keys($es[$type]);
			$op = $op[0];
		}

		$es[$type][$op][] = $value;

		unset($filter['q']); // remove wordpress foo

		return self::_buildUrl($url, $filter);
	}

	/**
	 * Modifies the provided URL by removing query parameters that control faceting.
	 *
	 * @param string $url The URL of the page that supports F.E.S
	 * @param string $type The data point you wish to remove faceting for (ie: a field name or taxonomy name)
	 * @param string $value The value/slug that was provided in the URL (query parameters)
	 *
	 * @return string The URL modified to remove faceting for the provided data point
	 **/
	static function urlRemove($url, $type, $value)
	{
		$filter = $_GET;

		if (isset($filter['es'])) {
			$es = &$filter['es'];

			$operation = isset($es[$type]['and']) ? 'and' : 'or';

			if (isset($es[$type][$operation])) {
				$index = array_search($value, $es[$type][$operation]);

				if ($index !== false) {
					unset($es[$type][$operation][$index]);

					if (count($es[$type][$operation]) == 0) {
						unset($es[$type][$operation]);
					}

					if (count($es[$type]) == 0) {
						unset($es[$type]);
					}
				}
			}

			if (count($es) == 0) {
				unset($filter['es']);
			}
		}

		return self::_buildUrl($url, $filter);
	}

	static function isActive()
	{
		global $wp_query;

		return isset($wp_query->facets);
	}

	/**
	 * @internal
	 **/
	static function _buildFacetResult($type, $items, $createItem)
	{
		global $wp_query;

		$result = array(
			'selected' => array(),
			'available' => array(),
			'total' => 0,
			'max' => 0,
			'min' => 0
		);

		if (!self::isActive()) {
			return $result;
		}

		$facets = $wp_query->facets;

		if (isset($facets[$type])) {
			foreach ($items as $key => $value) {
				$item = $createItem($key, $value, $facets[$type]);

				$inand = isset($_GET['es'][$type]['and']) && in_array($item['slug'], $_GET['es'][$type]['and']);
				$inor = isset($_GET['es'][$type]['or']) && in_array($item['slug'], $_GET['es'][$type]['or']);

				if ($inand || $inor) {
					$result['selected'][$item['slug']] = $item;
				} else if (isset($facets[$type][$item['slug']])) {
					$count = $item['count'] = $facets[$type][$item['slug']];

					if ($count > 0) {
						$result['available'][$item['slug']] = $item;
						$result['total'] += $item['count'];

						if ($item['count'] > $result['max']) {
							$result['max'] = $item['count'];
						}

						if ($result['min'] == null || $item['count'] < $result['min']) {
							$result['min'] = $item['count'];
						}
					}
				}
			}
		}

		return $result;
	}

	/**
	 * @internal
	 **/
	static function _buildUrl($url, $query)
	{
		$parts = parse_url($url);

		if (isset($parts['port'])) {
			$url = sprintf("%s://%s:%d%s", $parts['scheme'], $parts['host'], $parts['port'], $parts['path']);
		} else {
			$url = sprintf("%s://%s%s", $parts['scheme'], $parts['host'], $parts['path']);
		}

		if (count($query) > 0) {
			$url .= "?";

			$url .= http_build_query($query);
		}

		return $url;
	}
}

?>
