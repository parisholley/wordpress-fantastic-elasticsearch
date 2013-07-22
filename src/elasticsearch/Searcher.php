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
	* @see Faceting
	* 
	* @return array The results of the search
	**/
	public function search($search, $pageIndex = 0, $size = 10, $facets = array()){
		$args = self::_buildQuery($search, $facets);

		if(empty($args)){
			return array(
				'total' => 0,
				'ids' => array(),
				'scores' => array(),
				'facets' => array()
			);
		}

		return self::_query($args, $pageIndex, $size);
	}

	/**
	* @internal
	**/
	public function _query($args, $pageIndex, $size, $type = null){
		$query =new \Elastica\Query($args);
		$query->setFrom($pageIndex * $size);
		$query->setSize($size);
		$query->setFields(array('id'));

		Config::apply_filters('elastica_query', $query);

		try{
			$index = Indexer::_index(false);

			$search = new \Elastica\Search($index->getClient());
			$search->addIndex($index);

			if($type){
				$search->addType($index->getType($type));
			}

			Config::apply_filters('elastica_search', $search);

			$results = $search->search($query);

			$val = self::_parseResults($results);

			return Config::apply_filters('query_response', $val, $results);
		}catch(\Exception $ex){
			error_log($ex);

			return null;
		}
	}

	/**
	* @internal
	**/
	public function _parseResults($response){
		$val = array(
			'total' => $response->getTotalHits(),
			'scores' => array(),
			'facets' => array()
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
			$val['scores'][$result->getId()] = $result->getScore();
		}

		$val['ids'] = array_keys($val['scores']);

		return Config::apply_filters('elastica_results', $val, $response);		
	}

	/**
	* @internal
	**/
	public function _buildQuery($search, $facets = array()){
		$shoulds = array();
		$musts = array();
		$filters = array();

		foreach(Config::taxonomies() as $tax){
			if($search){
				$score = Config::score('tax', $tax);

				if($score > 0){
					$shoulds[] = array('text' => array( $tax => array(
						'query' => $search,
						'boost' => $score
					)));
				}
			}

			self::_filterBySelectedFacets($tax, $facets, 'term', $musts, $filters);
		}

		$args = array();

		$numeric = Config::option('numeric');

		foreach(Config::fields() as $field){
			if($search){
				$score = Config::score('field', $field);

				if($score > 0){
					$shoulds[] = array('text' => array($field => array(
						'query' => $search,
						'boost' => $score
					)));
				}
			}

			if(isset($numeric[$field]) && $numeric[$field]){
				$ranges = Config::ranges($field);

				if(count($ranges) > 0 ){
					self::_filterBySelectedFacets($field, $facets, 'range', $musts, $filters, $ranges);
				}
			}
		}

		if(count($shoulds) > 0){
			$args['query']['bool']['should'] = $shoulds;
		}

		if(count($filters) > 0){
			$args['filter']['bool']['should'] = $filters;
		}

		if(count($musts) > 0){
			$args['query']['bool']['must'] = $musts;
		}

		$args = Config::apply_filters('query_pre_facet_filter', $args);

		// return facets
		foreach(Config::facets() as $facet){
			$args['facets'][$facet]['terms']['field'] = $facet;
		}

		if(is_array($numeric)){
			foreach(array_keys($numeric) as $facet){
				$ranges = Config::ranges($facet);

				if(count($ranges) > 0 ){
					$args['facets'][$facet]['range'][$facet] = array_values($ranges);
				}
			}
		}
		
		return Config::apply_filters('query_post_facet_filter', $args);
	}

	/**
	* @internal
	**/
	public function _filterBySelectedFacets($name, $facets, $type, &$musts, &$filters, $translate = array()){
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
