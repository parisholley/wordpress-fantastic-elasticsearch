<?php
namespace elasticsearch;


/**
 *  Walker class for names
 */
class Walker_TaxonomiesNames extends \Walker_Category{

	function start_lvl( &$output, $depth = 0, $args = array() ) { $output .= ""; }	

  	public function start_el(&$output, $term, $depth, $args){
  		global $NHP_Options;
	    $args = wp_parse_args($args);  extract($args);
	    $counter = charts_dynamic_counter($show_count,$name,$term); 	
	    ob_start();   
	    echo $name.",";
	    $output .= ob_get_clean();
	}

	function end_el( &$output, $page, $depth = 0, $args = array() ) { return; }
	function end_lvl( &$output, $depth = 0, $args = array() ) {	$output .= ""; }
}


/**
 *  Walker class for count
 */
class Walker_TaxonomiesCounts extends \Walker_Category{

	function start_lvl( &$output, $depth = 0, $args = array() ) { $output .= ""; }	

  	public function start_el(&$output, $term, $depth, $args){
  		global $NHP_Options;
	    $args = wp_parse_args($args);  extract($args);
	    $counter = charts_dynamic_counter($show_count,$name,$term); 	
	    ob_start();   
	    echo $counter.",";
	    $output .= ob_get_clean();
	}

	function end_el( &$output, $page, $depth = 0, $args = array() ) { return; }
	function end_lvl( &$output, $depth = 0, $args = array() ) {	$output .= ""; }
}

