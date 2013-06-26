<?php
namespace elasticsearch;

class Searcher{
	public function query($search, $pageIndex, $size, $facets = array()){
		$shoulds = array();
		$musts = array();
		$filters = array();
		$bytype = null;

		foreach(Api::types() as $type){
			if($type == $search){
				$bytype = $search;
				$search = null;
			}
		}

		foreach(Api::taxonomies() as $tax){
			if($search){
				$score = Api::score('tax', $tax);

				if($score > 0){
					$shoulds[] = array('text' => array( $tax => array(
						'query' => $search,
						'boost' => $score
					)));
				}
			}

			self::facet($tax, $facets, 'term', $musts, $filters);
		}

		$args = array();

		$numeric = Api::option('numeric');

		foreach(Api::fields() as $field){
			if($search){
				$score = Api::score('field', $field);

				if($score > 0){
					$shoulds[] = array('text' => array($field => array(
						'query' => $search,
						'boost' => $score
					)));
				}
			}

			if($numeric[$field]){
				$ranges = Api::ranges($field);

				if(count($ranges) > 0 ){
					self::facet($field, $facets, 'range', $musts, $filters, $ranges);
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

		foreach(Api::facets() as $facet){
			$args['facets'][$facet]['terms']['field'] = $facet;

			if(count($filters) > 0){
				foreach($filters as $filter){
					if(!$filter['term'][$facet]){
						$args['facets'][$facet]['facet_filter']['bool']['should'][] = $filter;
					}
				}
			}
		}
		
		$args = \apply_filters('es_query_args', $args);

		foreach(array_keys($numeric) as $facet){
			$ranges = Api::ranges($facet);

			if(count($ranges) > 0 ){
				$args['facets'][$facet]['range'][$facet] = array_values($ranges);
				
				if(count($filters) > 0){
					foreach($filters as $filter){
						$args['facets'][$facet]['facet_filter']['bool']['should'][] = $filter;
					}
				}
			}
		}

		$args = \apply_filters('es_query_args', $args);

		$query =new \Elastica_Query($args);
		$query->setFrom($pageIndex * $size);
		$query->setSize($size);
		$query->setFields(array('id'));

		//Possibility to modify the query after it was built
		\apply_filters('elastica_query', $query);

		try{
			$index = Api::index(false);

			$search = new \Elastica_Search($index->getClient());
			$search->addIndex($index);

			if($bytype){
				$search->addType($index->getType($bytype));
			}

			\apply_filters( 'elastica_pre_search', $search );

			$response = $search->search($query);
		}catch(\Exception $ex){
			return null;
		}


		$val = array(
			'total' => $response->getTotalHits(),
			'scores' => array(),
			'facets' => array()
		);

		foreach($response->getFacets() as $name => $facet){
			foreach($facet['terms'] as $term){
				$val['facets'][$name][$term['term']] = $term['count'];
			}
			foreach($facet['ranges'] as $range){
				$val['facets'][$name][$range['from'] . '-' . $range['to']] = $range['count'];
			}
		}

		foreach($response->getResults() as $result){
			$val['scores'][$result->getId()] = $result->getScore();
		}

		$val['ids'] = array_keys($val['scores']);

		//Possibility to alter the results
		return \apply_filters('elastica_results', $val, $response);
	}

	protected function facet($name, $facets, $type, &$musts, &$filters, $translate = array()){
		if(is_array($facets[$name])){
			foreach($facets[$name] as $operation => $facet){
				if(is_string($operation)){
					if($operation == 'and'){
						if(is_array($facet)){
							foreach($facet as $value){
								$musts[] = array( $type => array( $name => $translate[$value] ?: $value ));
							}
						}else{
							$musts[] = array( $type => array( $name => $translate[$facet] ?: $facet ));
						}
					}

					if($operation == 'or'){
						if(is_array($facet)){
							foreach($facet as $value){
								$filters[] = array( $type => array( $name => $translate[$value] ?: $value ));
							}
						}else{
							$filters[] = array( $type => array( $name => $translate[$facet] ?: $facet ));
						}
					}
				}else{
					$musts[] = array( $type => array( $name => $translate[$facet] ?: $facet ));
				}
			}
		}elseif($facets[$name]){
			$musts[] = array( $type => array( $name => $translate[$facets[$name]] ?: $facets[$name] ));
		}
	}
}
?>
