<?php
namespace elasticsearch;

class Search extends AbstractArchive
{
	function facets($wp_query, $args)
	{
		if (!is_search() || !Config::option('enable')) {
			return;
		}

		return $args;
	}
}

new Search();
?>
