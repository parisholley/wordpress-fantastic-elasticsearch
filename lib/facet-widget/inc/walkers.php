<?php
namespace elasticsearch;


/**
 *  Walker class for dropdown taxonomies
 */

class Walker_TaxonomiesDropdown extends \Walker_CategoryDropdown{


	function start_el(&$output, $category, $depth, $args) {
		$pad = str_repeat('&nbsp;', $depth * 3);
		$cat_name = apply_filters('list_cats', $category->name, $category);
		$counter = tfw_dynamic_counter($args['show_count'],$args['name'],$category);
		
		// Do not output it if the counter is empty and hide_empty is TRUE
		if (!(!$counter && $args['hide_empty'])){ 
			$output .= "\t<option class=\"level-$depth\" value=\"".$category->slug."\"";
			if ( $category->slug === (string) $args['selected'] ){
				$output .= ' selected="selected"';
			}
			$output .= '>';
			$output .= $pad.$cat_name;
			$output .= ($args['show_count'] == "none") ? '' : '&nbsp;('.  $counter .')';
			$output .= "</option>\n";
		} //end if counter
	}
 
} 


/**
 *  Walker class for Select Multiple
 */
class Walker_TaxonomiesMultiselect extends \Walker_Category{

	function start_lvl( &$output, $depth = 0, $args = array() ) { $output .= ""; }	

  	public function start_el(&$output, $term, $depth, $args){
  		$pad = str_repeat('&nbsp;', $depth * 3);
	    $args = wp_parse_args($args); extract($args);
	    global $NHP_Options;
	    $counter = tfw_dynamic_counter($show_count,$name,$term);
	    $query = Api::parse_query($_SERVER['QUERY_STRING']);
	    $elasticsearch = $name.$NHP_Options->get('multiple_relation');
		// Do not output it if the counter is empty and hide_empty is TRUE
	    if (!(!$counter && $hide_empty)){
		   	ob_start(); ?> 
		   	 <li class = 'multiselect_list'>		 
	      		<option value="<?php echo $term->slug; ?>" <?php if (is_array($query[$name]) && in_array($term->slug, $query[$name])) echo 'selected="selected"'; ?>>
	      			<?php echo $term->name; echo ($show_count == "none") ? '' : '&nbsp;<span>('.  $counter .')</span>';?>
	      		</option>		 
			</li>     
			<?php 
		    $output .= ob_get_clean();
	    } //end if counter
	}

  	function end_el( &$output, $page, $depth = 0, $args = array() ) { return; }
	function end_lvl( &$output, $depth = 0, $args = array() ) {	$output .= ""; }
}


/**
 *  Walker class for checklists
 */
class Walker_TaxonomiesChecklist extends \Walker_Category{

	function start_lvl( &$output, $depth = 0, $args = array() ) 
	{ $output .= "<li class = 'checkboxes_list'><ul class='children'>"; }	

  	public function start_el(&$output, $term, $depth, $args){
  		global $NHP_Options;
	    $args = wp_parse_args($args);  extract($args);
	    $counter = tfw_dynamic_counter($show_count,$name,$term);
	  	$query = Api::parse_query($_SERVER['QUERY_STRING']);
	  	$elasticsearch = $name.$NHP_Options->get('multiple_relation');
	  	
		// Do not output it if the counter is empty and hide_empty is TRUE
	    if (!(!$counter && $hide_empty)){
		    ob_start(); ?>   
		    <li class = 'checkboxes_list'>
		    	<label for="<?php echo $term->slug; ?>">
		      		<input type="checkbox" <?php checked(is_array($query[$name]) && in_array($term->slug, $query[$name])); ?> name="<?php echo $elasticsearch; ?>[]" id="<?php echo $term->slug; ?>"  value="<?php echo $term->slug; ?>" /> <?php echo $term->name; 
		      		echo ($show_count == "none") ? '' : '&nbsp;<span>('.  $counter .')</span>'; 
			?>	</label>  
			</li>     
			<?php 
		    $output .= ob_get_clean();
	    } //end if counter
	}

  	function end_el( &$output, $page, $depth = 0, $args = array() ) { return; }
	function end_lvl( &$output, $depth = 0, $args = array() ) {	$output .= "</ul></li>"; }
}

/**
 *  Walker class for radio buttons
 */
class Walker_TaxonomiesRadio extends \Walker_Category{

	function start_lvl( &$output, $depth = 0, $args = array() ) { $output .= "<li><ul class='children'>"; }	

	public function start_el(&$output, $term, $depth = 0, $args = array()){

	    $args = wp_parse_args($args);  extract($args);
	    $counter = tfw_dynamic_counter($show_count,$name,$term);
	    $checked = get_query_var($name);
	
		// Do not output it if the counter is empty and hide_empty is TRUE
	    if (!(!$counter && $hide_empty)){
		    ob_start(); ?>   
		    <li>
		    	<label for="<?php echo $term->slug; ?>">
		      		<input type="radio" <?php checked($term->slug == $checked); ?> id="<?php echo $term->slug; ?>" name="<?php echo $name; ?>" value="<?php echo $term->slug; ?>" /> <?php echo esc_attr($term->name); 
					echo ($show_count == "none") ? '' : '&nbsp;<span>('.  $counter .')</span>'; 
			?>  </label>  
			</li>     
			<?php 
		    $output .= ob_get_clean();
		  } //end if counter
	} 

  	function end_el( &$output, $page, $depth = 0, $args = array() ) { return; }
	function end_lvl( &$output, $depth = 0, $args = array() ) { $output .= "</ul></li>"; }
}

