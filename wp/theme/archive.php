<?php
namespace elasticsearch;

class Archive extends AbstractArchive
{
	function facets($wp_query, $args)
	{
		$enabled = Config::option('enable_all_posts', false);

		if (!$enabled || !is_post_type_archive() || !isset($wp_query->query_vars['post_type'])) {
			return;
		}

		$fields = Config::fields();

		// should handle this better, good for now
		if (!in_array('post_type', $fields)) {
			die('You must re-index your data with the post_type field enabled to use this ElasticSearch on this post type.');
		}

		$args['post_type'] = $wp_query->query_vars['post_type'];

		return $args;
	}
}

new Archive();
?>
