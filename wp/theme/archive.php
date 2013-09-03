<?php
namespace elasticsearch;

class Archive extends AbstractArchive{
	function facets($wp_query, $args){
		$enabled = Config::option('enable_all_posts', false);

		if(!$enabled || !is_archive() || !isset($wp_query->query_vars['post_type']) ){
			return;
		}

		$args['post_type'] = $wp_query->query_vars['post_type'];

		return $args;
	}
}

new Archive();
?>
