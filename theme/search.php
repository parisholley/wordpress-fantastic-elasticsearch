<?php
namespace elasticsearch;

class Search{
	var $searched = false;
	var $total = 0;
	var $scores = array();
	var $page = 1;

	function __construct(){
		add_action('pre_get_posts', array(&$this, 'do_search'));
		add_action('the_posts', array(&$this, 'process_search'));
	}

	function do_search($wp_query){
		$this->searched = false;

		if(!$wp_query->is_main_query() || !is_search() || is_admin() || !Api::option('enable')){
			return;
		}

		$search = $wp_query->query_vars['s'];
		$this->page = $wp_query->query_vars['paged'] > 0 ? $wp_query->query_vars['paged'] - 1 : 0;

		$string = new \Elastica_Query_QueryString();
		$string->setDefaultOperator('AND');
		$string->setQuery($search);

		$shoulds = array();
		$musts = array();

		foreach(Api::taxonomies() as $tax){
			$score = Api::score('tax', $tax);

			if($score > 0){
				$shoulds[] = array('text' => array( $tax => array(
					'query' => $search,
					'boost' => $score
				)));
			}

			// faceting
			foreach($wp_query->query_vars[$tax] as $facet){
				$musts[] = array( 'term' => array( $tax => $facet ));
			}
		}

		foreach(Api::fields() as $field){
			$score = Api::score('field', $field);

			if($score > 0){
				$shoulds[] = array('text' => array($field => array(
					'query' => $search,
					'boost' => $score
				)));
			}
		}

		$args = array(
			'query' => array(
				'bool' => array(
					'should' => $shoulds
				)
			)
		);

		if(count($musts) > 0){
			$args['query']['bool']['must'] = $musts;
		}

		foreach(Api::facets() as $facet){
			$args['facets'][$facet]['terms']['field'] = $facet;
		}

		$query =new \Elastica_Query($args);

		if(!$wp_query->query_vars['posts_per_page']){
			$wp_query->query_vars['posts_per_page'] = get_option('posts_per_page');
		}

		$query->setFrom($this->page * $wp_query->query_vars['posts_per_page']);
		$query->setSize($perpage);
		$query->setFields(array('id', 'title'));

		try{
			$response = Api::index(false)->search($query);
		}catch(\Exception $ex){
			return;
		}

		$facets = array();

		foreach($response->getFacets() as $name => $facet){
			foreach($facet['terms'] as $term){
				$facets[$name][$term['term']] = $term['count'];
			}
		}

		$this->scores = array();

		foreach($response->getResults() as $result){
			$this->scores[$result->getId()] = $result->getScore();
		}

		$this->total = $response->getTotalHits();

		$wp_query->query_vars['post__in'] = array_keys($this->scores);
		$wp_query->query_vars['paged'] = 1;
		$wp_query->facets = $facets;
		
		$this->searched = true;	
	}

	function process_search($posts){
		global $wp_query;

		if($this->searched){
			$this->searched = false;

			$wp_query->max_num_pages = ceil( $this->total / $wp_query->query_vars['posts_per_page'] );
			$wp_query->found_posts = $this->total;
			$wp_query->query_vars['paged'] = $this->page + 1;

			usort($posts, array(&$this, 'sort_posts'));
		}

		return $posts;
	}

	function sort_posts($a, $b){
		return $this->scores[$a->ID] > $this->scores[$b->ID] ? -1 : 1;
	}
}

new Search();
?>