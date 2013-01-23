<?php
namespace elasticsearch;

class Searcher{
	public function query($search, $pageIndex, $size, $facets = array()){
		$shoulds = array();
		$musts = array();
		$type = null;

		foreach(Api::types() as $type){
			if($type == $search){
				$type = $search;
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

			// faceting
			foreach($facets[$tax] as $facet){
				$musts[] = array( 'term' => array( $tax => $facet ));
			}
		}

		if($search){
			foreach(Api::fields() as $field){
				$score = Api::score('field', $field);

				if($score > 0){
					$shoulds[] = array('text' => array($field => array(
						'query' => $search,
						'boost' => $score
					)));
				}
			}
		}

		$args = array();

		if(count($shoulds) > 0){
			$args['query']['bool']['should'] = $shoulds;
		}

		if(count($musts) > 0){
			$args['query']['bool']['must'] = $musts;
		}

		foreach(Api::facets() as $facet){
			$args['facets'][$facet]['terms']['field'] = $facet;
		}

		$query =new \Elastica_Query($args);
		$query->setFrom($pageIndex * $size);
		$query->setSize($size);
		$query->setFields(array('id'));

		try{
			$index = Api::index(false);

			if($type){
				$response = $index->getType($type)->search($query);
			}else{
				$response = $index->search($query);
			}
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
		}

		foreach($response->getResults() as $result){
			$val['scores'][$result->getId()] = $result->getScore();
		}

		$val['ids'] = array_keys($val['scores']);

		return $val;
	}
}
?>