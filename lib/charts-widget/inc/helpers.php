<?php
namespace elasticsearch;

/*
* Updates the counter for each term based on the current Query and the name of the term
* Called inside custom Walkers
*/
function charts_dynamic_counter($show_count = "total", $taxonomy = '', $term ){

	// When the counter is dynamic, get the global $query_string, 
	// and add our term to which we want to display the post count 
	// and return the number of posts found if our term would have been selected

	global $wp_query;

	$post_type_taxonomies = isset($_GET['post_type']) ? get_object_taxonomies( esc_attr($_GET['post_type']) ) : array();

	if ( $wp_query->is_main_query() ){	

		$facets = Template::facets(); 
		
		return $facets[$term->taxonomy][$term->slug];	
	}else {
		return 0;
	}
}
