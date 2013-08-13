<?php
namespace elasticsearch;

/**
* This class provides numerous helper methods for working with facet information returned from elastic search results.
*
* @license http://opensource.org/licenses/MIT
* @author Paris Holley <mail@parisholley.com>
* @version 2.0.0
**/
class Faceting{
	/**
	* A convenient method that aggregates the results of all the other methods in the class. Example of output:
	*
	* <code>
	* 	array(
	*		// the keys are names of fields and/or taxonomies
	* 		'taxonomy' => array(
	* 			'available' => array(
	* 				'taxonomy1'	=> array(
	* 					'count'	=> 10,
	* 					'slug'	=> 'taxonomy1',
	* 					'name'	=> 'Taxonomy One',
	* 					'font'	=> 24
	* 				)
	* 			),
	* 			'selected' => array(
	* 				'taxonomy2'	=> array(
	* 					'slug'	=> 'taxonomy2',
	* 					'name'	=> 'Taxonomy Two'
	* 				)
	* 			),
	* 			'total' => 10
	* 		),
	* 		'rating' => array(
	* 			'available' => array(
	* 				'10-20' => array(
	* 					'count'	=> 4,
	* 					'slug'	=> '10-20',
	* 					'to'	=> 20,
	* 					'from'	=> 10
	* 				)			
	* 			),
	* 			'total' => 4
	* 		)
	* 	)
	* </code>
	* 
	* @param string $minFont The minimum font size to use for display in a tag cloud (defaults to : 12)
	* @param string $maxFont The maximum font size to use for display in a tag cloud (defaults to : 24)
	* 
	* @return array An associative array where the keys represent the data point with a list of selected and/or available options.
	**/
	static function all($minFont = 12, $maxFont = 24){
		$options = array();

		foreach(Config::taxonomies() as $tax){
			$options[$tax] = self::taxonomy($tax);
		}

		$numeric = Config::option('numeric');

		foreach(Config::fields() as $field){
			if(isset($numeric[$field])){
				$options[$field] = self::range($field);
			}
		}

		foreach($options as $name => &$field){
			foreach($field['available'] as &$available){
				$available['font'] = self::cloud($field['available'], $available, $minFont, $maxFont);
			}
		}

		return $options;
	}

	/**
	* Analyse query parameters for range slugs and determine which facets are selected vs. which are available for the given field. Example of output:
	* 
	* <code>
	* 	array(
	* 		'available' => array(
	* 			'10-20' => array(
	* 				'count'	=> 4,
	* 				'slug'	=> '10-20',
	* 				'to'	=> 20,
	* 				'from'	=> 10
	* 			)			
	* 		),
	* 		'selected' => array(
	* 			'-20' => array(
	* 				'slug'	=> '-20',
	* 				'to'	=> 20
	* 			)			
	* 		),
	* 		'total' => 4
	* 	)
	* 	</code>
	* 
	* @param string $field The field to determine range facet information about
	*
	* @return array An associative array based on example provided
	**/
	static function range($field){
		global $wp_query;

		$facets = $wp_query->facets;

		$result = array(
			'selected' => array(),
			'available' => array(),
			'total' => 0
		);

		$ranges = Config::ranges($field);

		if($ranges){
			foreach($ranges as $slug => $range){
				$split = explode('-', $slug);

				$item = array(
					'slug' => $slug,
					'count' => $facets[$field][$slug],
					'to' => $split[1],
					'from' => $split[0]
				);

				if(isset($_GET[$field]) && in_array($slug, $_GET[$field]['and'])){
					$result['selected'][$slug] = $item;
				}else if($item['count'] > 0){
					$result['available'][$slug] = $item;
					$result['total'] += $item['count'];
				}
			}
		}

		return $result;
	}

	/**
	* Analyse query parameters for taxonomoy slugs and determine which facets are selected vs. which are available for the given field. Example of output:
	* 
	* <code>
	* 	array(
	* 		'available' => array(
	* 			'taxonomy1' => array(
	* 				'count' => 10,
	* 				'slug'	=> 'taxonomy1',
	* 				'name'	=> 'Taxonomy One',
	* 				'font'	=> 24
	* 			)
	* 		),
	* 		'selected' => array(
	* 			'taxonomy2'	=> array(
	* 				'slug'	=> 'taxonomy2',
	* 				'name'	=> 'Taxonomy Two'
	* 			)
	* 		),
	* 		'total' => 10
	* 	)
 	* 	</code>
	* 
	* @param string $field The taxonomy type to retrieve facet information about
	* 
	* @return array An associative array based on example provided
	**/
	static function taxonomy($tax){
		global $wp_query;

		$facets = $wp_query->facets;

		$taxonomy = array(
			'selected' => array(),
			'available' => array(),
			'total' => 0
		);

		if(isset($facets[$tax])){
			foreach(get_terms($tax) as $term){
				$item = array(
					'name' => $term->name ?: $term->slug,
					'slug' => $term->slug
				);

				if(isset($_GET[$tax]) && in_array($term->slug, $_GET[$tax]['and'])){
					$taxonomy['selected'][$term->slug] = $item;
				}else if(isset($facets[$tax][$term->slug])){
					$count = $item['count'] = $facets[$tax][$term->slug];

					if($count > 0){
						$taxonomy['available'][$term->slug] = $item;
						$taxonomy['total'] += $item['count'];
					}
				}
			}
		}

		return $taxonomy;
	}

	/**
	* Will calculate a font size based on the total number of results for the given item in a collection of items. Example of output:
	* 
	* <code>
	* 	array(
	* 		'available' => array(
	* 			'taxonomy1' => array(
	* 				'count' => 10,
	* 				'slug'	=> 'taxonomy1',
	* 				'name'	=> 'Taxonomy One',
	* 				'font'	=> 24
	* 			)
	* 		),
	* 		'selected' => array(
	* 			'taxonomy2' => array(
	* 				'slug'	=> 'taxonomy2',
	* 				'name'	=> 'Taxonomy Two'
	* 			)
	* 		),
	* 		'total' => 10
	* 	)
 	* </code>
	* 
	* @param array $items An array of arrays that contain a key called 'count'
	* @param array $item An item out of the array that you wish to calculate a font size
	* @param string $minFont The minimum font size to use for display in a tag cloud (defaults to : 12)
	* @param string $maxFont The maximum font size to use for display in a tag cloud (defaults to : 24)
	* 
	* @return integer The calculated font size
	**/
	static function cloud($items, $item, $min = 12, $max = 24){
		$maxTotal = 1;

		foreach($items as $itm){
			if(log($itm['count']) > $maxTotal){
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
	static function urlAdd($url, $type, $value, $operation = 'and'){
		$filter = $_GET;

		$op = $operation;

		if(isset($filter[$type])){
			$op = array_keys($filter[$type]);
			$op = $op[0];
		}

		$filter[$type][$op][] = $value;

		$url = new \Purl\Url($url);
		$url->query->setData($filter);

		return $url->getUrl();
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
	static function urlRemove($url, $type, $value){
		$filter = $_GET;

		$operation = isset($filter[$type]['and']) ? 'and' : 'or';

		if(isset($filter[$type][$operation])){
			$index = array_search($value, $filter[$type][$operation]);

			if($index !== false){
				unset($filter[$type][$operation][$index]);

				if(count($filter[$type][$operation]) == 0){
					unset($filter[$type][$operation]);
				}

				if(count($filter[$type]) == 0){
					unset($filter[$type]);
				}
			}
		}

		$url = new \Purl\Url($url);
		$url->query->setData($filter);

		return $url->getUrl();
	}
}

?>
