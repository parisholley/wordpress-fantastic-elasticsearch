<?php
namespace elasticsearch;

class Category extends AbstractArchive
{
	function facets($wp_query, $args)
	{
		if (!is_category()) {
			return;
		}

		$enabled = Config::option('enable_categories', array());
		$all = Config::option('enable_all_categories', false);

		$cats = array();

		if (!$wp_query->is_main_query() || is_admin()) {
			return;
		}

		if (isset($wp_query->query_vars['category_name']) && !empty($wp_query->query_vars['category_name'])) {
			$cat = get_category_by_slug($wp_query->query_vars['category_name']);

			if (!$all && (!$cat || !in_array($cat->term_id, $enabled))) {
				return;
			}

			$cats[] = $cat;
		} else if (isset($wp_query->query_vars['cat']) && !empty($wp_query->query_vars['cat'])) {
			$catids = explode(',', $wp_query->query_vars['cat']);

			foreach ($catids as $id) {
				if (!$all && !in_array($id, $enabled)) {
					return;
				}

				$cats[] = get_category($id);
			}
		}

		if (empty($cats)) {
			return;
		}

		if (!isset($args['category'])) {
			if (count($cats) > 1) {
				foreach ($cats as $cat) {
					$args['category']['or'][] = $cat->slug;
				}
			} else {
				$args['category']['and'][] = $cats[0]->slug;
			}
		}

		return $args;
	}
}

new Category();
?>
