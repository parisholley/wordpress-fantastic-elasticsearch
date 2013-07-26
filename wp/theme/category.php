<?php
namespace elasticsearch;

class Category{
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

		$cat = null;

		if(isset($wp_query->query_vars['category_name'])){
			$cat = get_category_by_slug($wp_query->query_vars['category_name']);
		}

		if(isset($wp_query->query_vars['cat'])){
			$cat = get_category($wp_query->query_vars['cat']);
		}

		$enabled = Config::option('enable_categories');
		
		if(!$wp_query->is_main_query() || !(is_tax() || $cat) || is_admin() || !$enabled || !in_array($cat->term_id, $enabled)){
			return;
		}

		$args = $_GET;

		if(!isset($args['category'])){
			$args['category']['or'][] = $cat->slug;
		}

		$this->page = isset($wp_query->query_vars['paged']) && $wp_query->query_vars['paged'] > 0 ? $wp_query->query_vars['paged'] - 1 : 0;

		if(!isset($wp_query->query_vars['posts_per_page'])){
			$wp_query->query_vars['posts_per_page'] = get_option('posts_per_page');
		}

		$results = Searcher::search(null, $this->page, $wp_query->query_vars['posts_per_page'], $args);

		if($results == null){
			return null;
		}

		$this->total = $results['total'];
		$this->ids = $results['ids'];
		
		$wp_query->query_vars['s'] = '';	
		# do not show results if none were returned
		$wp_query->query_vars['post__in'] = empty($results['ids']) ? array(-1) : $results['ids'];
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
		return array_search($b->ID, $this->ids) > array_search($a->ID, $this->ids) ? -1 : 1;
	}
}

new Category();
?>
