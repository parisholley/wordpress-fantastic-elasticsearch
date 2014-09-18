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

		$cats = array();

		$enabled = Config::option('enable_categories');
		
		if(!$wp_query->is_main_query() || is_admin() || !is_category() || !$enabled) {
			return;
		}

		if(isset($wp_query->query_vars['category_name']) && !empty($wp_query->query_vars['category_name'])){
			$cat = get_category_by_slug($wp_query->query_vars['category_name']);

			if(isset($enabled) && !in_array($cat->term_id, $enabled)){
				return;
			}

			$cats[] = $cat;
		}

		if(isset($wp_query->query_vars['cat'])){
			$catids = explode(',', $wp_query->query_vars['cat']);

			foreach($catids as $id){
				if(isset($enabled) && !in_array($id, $enabled)){
					return;
				}

				$cats[] = get_category($id);
			}
		}
		
		if (empty($cats)) {
			return;
		}

		$args = $_GET;

		if(!isset($args['category'])){
			if(count($cats) > 1){
				foreach($cats as $cat){
					$args['category']['or'][] = $cat->slug;
				}
			}else{
				$args['category']['and'][] = $cats[0]->slug;
			}
		}

		$this->page = isset($wp_query->query_vars['paged']) && $wp_query->query_vars['paged'] > 0 ? $wp_query->query_vars['paged'] - 1 : 0;

		if(!isset($wp_query->query_vars['posts_per_page'])){
			$wp_query->query_vars['posts_per_page'] = get_option('posts_per_page');
		}

		$results = Searcher::search(null, $this->page, $wp_query->query_vars['posts_per_page'], $args, true);

		if($results == null){
			return null;
		}

		$this->total = $results['total'];
		$this->ids = $results['ids'];
		
		$wp_query->query_vars['s'] = '';	
		# do not show results if none were returned
		$wp_query->query_vars['post__in'] = empty($results['ids']) ? array(0) : $results['ids'];
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
