<?php
namespace elasticsearch;

/**
* The searcher class provides all you need to query your ElasticSearch server.
*
* @license http://opensource.org/licenses/MIT
* @author Paris Holley <mail@parisholley.com>
* @version 2.0.0
**/
class Searcher{
	/**
	* Initiate a search with the ElasticSearch server and return the results. Use Faceting to manipulate URLs.
	* @param string $search A space delimited list of terms to search for
	* @param integer $pageIndex The index that represents the current page
	* @param integer $size The number of results to return per page
	* @param array $facets An object that contains selected facets (typically the query string, ie: $_GET)
	* @param boolean $sortByDate If false, results will be sorted by score (relevancy)
	* @see Faceting
	* 
	* @return array The results of the search
	**/
	public static function search($search = '', $pageIndex = 0, $size = 10, $facets = array(), $sortByDate = false){
		$args = self::_buildQuery($search, $facets);

		if(empty($args) || (empty($args['query']) && empty($args['facets']))){
			return array(
				'total' => 0,
				'ids' => array(),
				'facets' => array()
			);
		}

		// need to do rethink the signature of the search() method, arg list can't just keep growing
		return self::_query($args, $pageIndex, $size, $sortByDate);
	}

	/**
	* @internal
	**/
	public static function _query($args, $pageIndex, $size, $sortByDate = false){
		$query =new \Elastica\Query($args);
		$query->setFrom($pageIndex * $size);
		$query->setSize($size);
		$query->setFields(array('id'));

		Config::apply_filters('searcher_query', $query);

		try{
			$index = Indexer::_index(false);

			$search = new \Elastica\Search($index->getClient());
			$search->addIndex($index);
      if (!$query->hasParam('sort')){
        if($sortByDate){
          $query->addSort(array('post_date' => 'desc'));
        }else{
          $query->addSort('_score');
        }
      }
			Config::apply_filters('searcher_search', $search, $query);

			$results = $search->search($query);

			return self::_parseResults($results);
		}catch(\Exception $ex){
			error_log($ex);

			return null;
		}
	}

	/**
	* @internal
	**/
	public static function _parseResults($response){
		$val = array(
			'total' => $response->getTotalHits(),
			'facets' => array(),
			'ids' => array()
		);

		foreach($response->getFacets() as $name => $facet){
			if(isset($facet['terms'])){
				foreach($facet['terms'] as $term){
					$val['facets'][$name][$term['term']] = $term['count'];
				}
			}

			if(isset($facet['ranges'])){
				foreach($facet['ranges'] as $range){
					$from = isset($range['from']) ? $range['from'] : '';
					$to = isset($range['to']) ? $range['to'] : '';

					$val['facets'][$name][$from . '-' . $to] = $range['count'];
				}
			}
		}

		foreach($response->getResults() as $result){
			$val['ids'][] = $result->getId();
		}

		return Config::apply_filters('searcher_results', $val, $response);		
	}

	/**
	* @internal
	**/
	public static function _buildQuery($search, $facets = array()){
		global $blog_id;

		$search = str_ireplace(array(' and ', ' or '), array(' AND ', ' OR '), $search);

		$fields = array();
		$musts = array();
		$filters = array();
		$scored = array();

		foreach(Config::taxonomies() as $tax){
			if($search){
				$score = Config::score('tax', $tax);

				if($score > 0){
					$scored[] = "{$tax}_name^$score";
				}
			}

			self::_filterBySelectedFacets($tax, $facets, 'term', $musts, $filters);
		}

		$args = array();

		$numeric = Config::option('numeric');

		$exclude = Config::apply_filters('searcher_query_exclude_fields', array('post_date'));

		$fields = Config::fields();

		self::_searchField($fields, 'field', $exclude, $search, $facets, $musts, $filters, $scored, $numeric);
		self::_searchField(Config::meta_fields(), 'meta', $exclude, $search, $facets, $musts, $filters, $scored, $numeric);

		if(count($scored) > 0 && $search){
			$qs = array(
				'fields' => $scored,
				'query' => $search
			);

			$fuzzy = Config::option('fuzzy');

			if($fuzzy && strpos($search, "~") > -1){
				$qs['fuzzy_min_sim'] = $fuzzy;
			}

			$qs = Config::apply_filters('searcher_query_string', $qs);

			$musts[] = array( 'query_string' => $qs );
		}

		if(in_array('post_type', $fields)){
			self::_filterBySelectedFacets('post_type', $facets, 'term', $musts, $filters);
		}

		if(count($filters) > 0){
			$args['filter']['bool']['should'] = $filters;
		}

		if(count($musts) > 0){
			$args['query']['bool']['must'] = $musts;
		}

		$args['filter']['bool']['must'][] = array( 'term' => array( 'blog_id' => $blog_id ) );

		$args = Config::apply_filters('searcher_query_pre_facet_filter', $args);

		if(in_array('post_type', $fields)){
			$args['facets']['post_type']['terms'] = array(
				'field' => 'post_type',
				'size' => Config::apply_filters('searcher_query_facet_size', 100)  // see https://github.com/elasticsearch/elasticsearch/issues/1832
			);
		}

		// return facets
		foreach(Config::facets() as $facet){
			$args['facets'][$facet]['terms'] = array(
				'field' => $facet,
				'size' => Config::apply_filters('searcher_query_facet_size', 100)  // see https://github.com/elasticsearch/elasticsearch/issues/1832
			);

			$args['facets'][$facet]['facet_filter'] = array( 'bool' => array( 'must' => array(
				array( 'term' => array( 'blog_id' => $blog_id ))
			)));

			if(count($filters) > 0){
				$applicable = array();

				foreach($filters as $filter){
					if(isset($filter['term']) && !in_array($facet, array_keys($filter['term']))){
						// do not filter on itself when using OR
						$applicable[] = $filter;
					}
				}

				if(count($applicable) > 0){
					$args['facets'][$facet]['facet_filter']['bool']['should'] = $applicable;
				}
			}
		}

		if(is_array($numeric)){
			foreach(array_keys($numeric) as $facet){
				$ranges = Config::ranges($facet);

				if(count($ranges) > 0 ){
					$args['facets'][$facet]['range'][$facet] = array_values($ranges);
					$args['facets'][$facet]['facet_filter'] = array( 'bool' => array( 'must' => array(
						array( 'term' => array( 'blog_id' => $blog_id ))
					)));
				}
			}
		}
		
		return Config::apply_filters('searcher_query_post_facet_filter', $args);
	}

	public static function _searchField($fields, $type, $exclude, $search, $facets, &$musts, &$filters, &$scored, $numeric){
		foreach($fields as $field){
			if(in_array($field, $exclude)){
				continue;
			}

			if($search){
				$score = Config::score($type, $field);
				$notanalyzed = Config::option('not_analyzed');

				if($score > 0){
					if(strpos($search, "~") > -1 || isset($notanalyzed[$field])){
						// TODO: fuzzy doesn't work with english analyzer
						$scored[] = "$field^$score";
					}else{
						$scored[] = "$field.english^$score";
					}
				}
			}

			if(isset($numeric[$field]) && $numeric[$field]){
				$ranges = Config::ranges($field);

				if(count($ranges) > 0 ){
					self::_filterBySelectedFacets($field, $facets, 'range', $musts, $filters, $ranges);
				}
			}
		}
	}

	/**
	* @internal
	**/
	public static function _filterBySelectedFacets($name, $facets, $type, &$musts, &$filters, $translate = array()){
		if(isset($facets[$name])){
			$output = &$musts;

			$facets = $facets[$name];

			if(!is_array($facets)){
				$facets = array($facets);
			}

			foreach($facets as $operation => $facet){
				if(is_string($operation) && $operation == 'or'){
					// use filters so faceting isn't affecting, allowing the user to select more "or" options
					$output = &$filters;
				}

				if(is_array($facet)){
					foreach($facet as $value){
						$output[] = array( $type => array( $name => isset($translate[$value]) ? $translate[$value] : $value ));
					}

					continue;
				}
				
				$output[] = array( $type => array( $name => isset($translate[$facet]) ? $translate[$facet] : $facet ));
			}
		}
	}
}
?>
