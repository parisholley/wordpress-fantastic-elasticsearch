<?php 
namespace elasticsearch;

echo $before_title . $title . $after_title; 
global $wp; 
$current_url = add_query_arg( $wp->query_string, '', home_url( $wp->request ) ); 
?>
	<form action="<?php echo esc_url( $current_url.'/'); ?>" role="search" method="get" class="taxonomies-filter-widget-form <?php if( Config::option(auto_submit) ) echo 'tfw_auto'; ?>"><div>
		<input type="hidden" name="post_type" value="<?php echo $tfw_post_type ?>" />
		<ul>
<?php
	foreach ($selected_filters as $filter) {
		switch($filter['mode']) {
	        case 'dropdown':	$this->taxonomy_dropdown_walker($filter['name']);	break;
	        case 'multiselect':	$this->print_multiselect_taxonomy($filter['name']);	break;
	        case 'checkbox':	$this->print_checkbox_taxonomy($filter['name']);	break;
	        case 'radio':		$this->print_radio_taxonomy($filter['name']);		break;
	    	} //end switch
	} //end foreach

	if( Config::option(display_search_box) && !empty($search_box_label) ){
	echo '<li class="search_box"><label class="taxlabel">'.$search_box_label.'</label><input type="text" name="s" class="input_search" value="'.get_search_query().'" /></li>';
	} 
	echo '<li>';
	global $wp;
	$current_url = add_query_arg( '', '', home_url( $wp->request ) );
	if( Config::option(display_reset_button) && !empty($reset_button_label) ){
		echo '<a class="reset_button" href="'. esc_url( $current_url.'/' ).'" >'.$reset_button_label.'</a>';
	} // end if

	if (Config::option(auto_submit)) {   echo '<noscript>';  } 
		echo '<input type="submit" value="'.$submit.'" />';
	if (Config::option(auto_submit)) {   echo '</noscript>';  } 
	
	echo '</li></ul></div></form>';
