<?php
namespace elasticsearch;

abstract class AbstractArchive
{
	var $searched = false;
	var $attempted = false;
	var $total = 0;
	var $scores = array();
	var $page = 1;
	var $search = '';

	function __construct()
	{
		add_action('pre_get_posts', array(&$this, 'do_search'), 100);
		add_action('the_posts', array(&$this, 'process_search'));
	}

	function do_search($wp_query)
	{
		if (!$wp_query->is_main_query() || is_admin() || $this->attempted) {
			return;
		}

		$this->attempted = true;

		$args = $this->facets($wp_query, isset($_GET['es']) ? $_GET['es'] : array());

		if ($args === null) {
			return;
		}

		$this->page = isset($wp_query->query_vars['paged']) && $wp_query->query_vars['paged'] > 0 ? $wp_query->query_vars['paged'] - 1 : 0;

		if (!isset($wp_query->query_vars['posts_per_page'])) {
			$wp_query->query_vars['posts_per_page'] = get_option('posts_per_page');
		}

		$this->search = isset($wp_query->query_vars['s']) ? urldecode(str_replace('\"', '"', $wp_query->query_vars['s'])) : '';

		$results = Searcher::search($this->search, $this->page, $wp_query->query_vars['posts_per_page'], $args, $this->search ? false : true);

		if ($results == null) {
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

	function process_search($posts)
	{
		global $wp_query;

		if ($this->searched) {
			$this->searched = false;

			$wp_query->max_num_pages = ceil($this->total / $wp_query->query_vars['posts_per_page']);
			$wp_query->found_posts = $this->total;
			$wp_query->query_vars['paged'] = $this->page + 1;
			$wp_query->query_vars['s'] = $this->search;

			usort($posts, array(&$this, 'sort_posts'));
		}

		return $posts;
	}

	function sort_posts($a, $b)
	{
		return array_search($b->ID, $this->ids) > array_search($a->ID, $this->ids) ? -1 : 1;
	}

	abstract function facets($wp_query, $existing);
}

?>
