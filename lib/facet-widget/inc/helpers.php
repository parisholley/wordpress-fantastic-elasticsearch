<?php
namespace elasticsearch;

/*
* Updates the counter for each term based on the current Query and the name of the term
* Called inside custom Walkers
*/
function tfw_dynamic_counter($show_count = "total", $taxonomy = '', $term ){

	// When the counter is dynamic, get the global $query_string, 
	// and add our term to which we want to display the post count 
	// and return the number of posts found if our term would have been selected

	global $wp_query;
	global $query_string;

	$post_type_taxonomies = isset($_GET['post_type']) ? get_object_taxonomies( esc_attr($_GET['post_type']) ) : array();

	if(is_singular() || !in_array( $taxonomy, $post_type_taxonomies ) ){
		$show_count = "total";
	}

	if ( ($show_count == "dynamic" || $show_count == "none" ) && $wp_query->is_main_query() ){	

		$facets = Template::facets(); 
		
		return $facets[$term->taxonomy][$term->slug];

	// When counter is set to total, just output the term->count	
	} elseif ($show_count == "total") {
		return $term->count;	
	} else {
		return 0;
	}
}


// Fix WordPress default names for category and post_tag
function fix_taxonomy_name($taxonomy){
		$name = '';
		switch ($taxonomy) {
			case 'category': 	$name = 'category_name'; 	break;
			case 'post_tag': 	$name = 'tag'; 				break;
			default: 			$name = $taxonomy;			break;
		}	
		return $name;
}
