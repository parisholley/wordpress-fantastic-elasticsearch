<?php
namespace elasticsearch;

class Post_type{
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

		$type = get_queried_object();

		$enabled = Api::option('enable_post_types');
		
		if(!$wp_query->is_main_query() || !(is_post_type_archive() || $slug) || is_admin() || !$enabled || !in_array($type->name, $enabled)){
			return;
		}

		$args = $_GET;

		if(!$args[$type->name]){
			$args[$type->name]['or'][] = $slug;
		}

		$this->page = $wp_query->query_vars['paged'] > 0 ? $wp_query->query_vars['paged'] - 1 : 0;

		if(!$wp_query->query_vars['posts_per_page']){
			$wp_query->query_vars['posts_per_page'] = get_option('posts_per_page');
		}

		$results = Searcher::query($search, $this->page, $wp_query->query_vars['posts_per_page'], $args);

		if($results == null){
			return null;
		}

		$this->total = $results['total'];
		$this->scores = $results['scores'];
		
		$wp_query->query_vars['post__in'] = $results['ids'];
		$wp_query->query_vars['paged'] = 1;
		$wp_query->facets = $results['facets'];

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

new Post_type();
?>