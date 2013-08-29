<?php
namespace elasticsearch;

class Category extends AbstractArchive{
	function facets($wp_query, $args){
		if(!is_category()){
			return;
		}

		$enabled = Config::option('enable_categories');

		$cats = array();

		if(isset($wp_query->query_vars['category_name']) && !empty($wp_query->query_vars['category_name'])){
			$cat = get_category_by_slug($wp_query->query_vars['category_name']);

			if(!in_array($cat->term_id, $enabled)){
				return null;
			}

			$cats[] = $cat;
		}else if(isset($wp_query->query_vars['cat'])){
			$catids = explode(',', $wp_query->query_vars['cat']);

			foreach($catids as $id){
				if(!in_array($id, $enabled)){
					return null;
				}

				$cats[] = get_category($id);
			}
		}
		
		if(empty($cats) || !$enabled){
			return;
		}

		if(!isset($args['category'])){
			if(count($cats) > 1){
				foreach($cats as $cat){
					$args['category']['or'][] = $cat->slug;
				}
			}else{
				$args['category']['and'][] = $cats[0]->slug;
			}
		}

		return $args;
	}
}

new Category();
?>
