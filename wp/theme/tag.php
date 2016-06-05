<?php
namespace elasticsearch;

class Tag extends AbstractArchive
{
	function facets($wp_query, $args)
	{
		$enabled = Config::option('enable_all_tags', false);

		if (!$enabled || !is_tag()) {
			return;
		}

		if (!isset($wp_query->query_vars['tag'])) {
			return;
		}

		$args['post_tag']['and'][] = $wp_query->query_vars['tag'];

		return $args;
	}
}

new Tag();
?>
