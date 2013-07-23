<?php
namespace elasticsearch;

require_once( plugin_dir_path(__FILE__) . '/inc/walkers.php' );
require_once( plugin_dir_path(__FILE__) . '/inc/helpers.php' );

class Taxonomies_Filter_Widget extends \WP_Widget {

	/*--------------------------------------------------*/
	/* Constructor
	/*--------------------------------------------------*/   

	/**
	 * Specifies the classname and description, instantiates the widget, 
	 * loads localization files, and includes necessary stylesheets and JavaScript.
	 */
	 
	public static $tfw_options;
	public $filters;

	public function __construct() {  

		parent::__construct(
			'taxonomies-filter-widget',
			__( 'Elasticsearch Facets', 'taxonomies-filter-widget' ),
			array(
				'classname'		=>	'taxonomies-filter-widget',
				'description'	=>	__( 'Filter posts by taxonomies.', 'taxonomies-filter-widget' )
			)
		);
		
		// Hooks fired when the Widget is activated and deactivated
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

		// Register admin styles and scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_scripts_styles' ) );
	
		// Register site styles and scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'register_widget_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_widget_styles' ) );

		// Register the function that will handle the ajax request 
		add_action('wp_ajax_get_term_childrens', array($this,'ajax_drilldown'));
		add_action('wp_ajax_nopriv_get_term_childrens', array($this,'ajax_drilldown')); 
		
		// if is admin, create the options page, otherwise, set the search results template
		if(is_main_query()) {
			add_filter('get_meta_sql', array( $this, 'cast_decimal_precision' ));
		}
		
	} // end constructor     

	/*--------------------------------------------------*/
	/* Widget Config Functions
	/*--------------------------------------------------*/
	
	/**
	 * Outputs the content of the widget.
	 *
	 * @param	array	args		The array of form elements
	 * @param	array	instance	The current instance of the widget
	 */
	public function widget( $args, $instance ) {

		extract( $args );
		is_array($this->tfw_options) && extract( $this->tfw_options );

		$title 				 = apply_filters('widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base);
		$selected_filters 	 = isset( $instance['selected_filters'] ) ? $instance['selected_filters'] : array();
		$tfw_post_type 		 = isset( $instance['tfw_post_type'] ) ? $instance['tfw_post_type'] : 'post';
		// labels
		$submit 			 = empty( $instance['submit'] ) ? '' : $instance['submit'];
		$select_all  		 = empty( $instance['select_all'] ) ? '' : $instance['select_all'];
       	$search_box_label 	 = empty( $instance['search_box_label'] ) ? '' : $instance['search_box_label'];
       	$reset_button_label  = empty( $instance['reset_button_label'] ) ? '' : $instance['reset_button_label'];

		
		$this->selected_filters = $selected_filters;

		echo $before_widget;
		include( plugin_dir_path( __FILE__ ) . '/views/widget.php' );
		echo $after_widget;
	} // end widget
	
	/**
	 * Processes the widget's options to be saved.
	 *
	 * @param	array	new_instance	The previous instance of values before the update.
	 * @param	array	old_instance	The new instance of values to be generated via the update.
	 */
	public function update( $new_instance, $old_instance ) {
	
		$instance = $old_instance;
		
		$instance['title'] 				= strip_tags($new_instance['title']);
		$instance['filters'] 			=!empty( $new_instance['filters']) ? $new_instance['filters'] : array();
		$instance['tfw_post_type'] 		= isset( $new_instance['tfw_post_type'] ) ? $new_instance['tfw_post_type'] : 'post';

		//labels
		$instance['submit'] 			= strip_tags($new_instance['submit']);
		$instance['select_all'] 		= strip_tags($new_instance['select_all']);
		$instance['search_box_label'] 	= strip_tags($new_instance['search_box_label']);
		$instance['reset_button_label'] = strip_tags($new_instance['reset_button_label']);

		$selected_filters = array();
		// Filter out the ones not selected
		foreach ($instance['filters'] as $single_filter) {
			if (array_key_exists( 'name', $single_filter )) {
				$selected_filters[] = $single_filter;
			}
		}
		$instance['selected_filters'] = $selected_filters;

		return $instance;
	} // end update
	
	/**
	 * Generates the administration form for the widget.
	 *
	 * @param	array	instance	The array of keys and values for the widget.
	 */
	public function form( $instance ) {
	
    	// Extracting definded values and defining default values for variables

	    $instance = wp_parse_args( (array) $instance, array( 
			'title' => '', 
			'filters' => array(), 
			'tfw_post_type' => 'post',
			'submit' => 'Search',
			'search_box_label' => 'Search For',
			'reset_button_label' => 'Reset All',
			'select_all' => 'Select All'
		));

		$title 				= esc_attr( $instance['title'] );
		$selected_filters	= isset( $instance['selected_filters'] ) ? $instance['selected_filters'] : array();
		$tfw_post_type 		= isset( $instance['tfw_post_type'] ) ? $instance['tfw_post_type'] : 'post';

		//labels
		$submit 			= esc_attr( $instance['submit'] );
		$select_all 		= esc_attr( $instance['select_all'] );
		$search_box_label 	= esc_attr( $instance['search_box_label'] );
		$reset_button_label = esc_attr( $instance['reset_button_label'] );


		// Get already selected filters and put them at the top
		$all_filters = $selected_filters;

		// Append all other taxonomies
		foreach ( $this->get_post_type_taxonomies($tfw_post_type) as $taxonomy ) {
			if ( !$this->in_array_r( $taxonomy, $selected_filters ) )
				$all_filters[] = array('name' => $taxonomy,'mode'=>'dropdown');
		}

		// Display the admin form
		include( plugin_dir_path(__FILE__) . '/views/admin.php' );	
		
	} // end form


	/*--------------------------------------------------*/
	/* Widget Specific Functions
	/*--------------------------------------------------*/
	
	/**
	 * Fired when the plugin is activated.
	 *
	 * @param		boolean	$network_wide	True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog.
	 */
	public function activate( $network_wide ) {
		
	} // end activate
	
	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @param	boolean	$network_wide	True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog 
	 */
	public function deactivate( $network_wide ) {

		delete_option( 'widget_taxonomies-filter-widget' );
		delete_option( 'tfw_options' );
	} // end deactivate
	

	/**
	 * Registers and enqueues admin-specific JavaScript and CSS only on widgets page.
	 */	
	public function register_admin_scripts_styles($hook) {

		if( 'widgets.php' != $hook )
        	return;
		wp_enqueue_script( 'taxonomies-filter-widget-admin-script', plugins_url( ES_PLUGIN_DIR.'/wp/lib/facet-widget/js/admin.js' ), array('jquery'), false, true );
		wp_enqueue_style( 'taxonomies-filter-widget-admin-styles', plugins_url( ES_PLUGIN_DIR.'/wp/lib/facet-widget/css/admin.css' ) );
		
	} // end register_admin_scripts
	
	/**
	 * Registers and enqueues widget-specific scripts.
	 */
	public function register_widget_scripts() {
		global $NHP_Options;

		wp_enqueue_script('jquery');
		wp_enqueue_script( 'taxonomies-filter-widget-slider-script', plugins_url( ES_PLUGIN_DIR.'/wp/lib/facet-widget/js/jquery.nouislider.min.js' ), array('jquery'), false, true );
		wp_enqueue_script( 'taxonomies-filter-widget-script', plugins_url( ES_PLUGIN_DIR.'/wp/lib/facet-widget/js/widget.js' ), array('jquery'), false, true );
		wp_localize_script( 'taxonomies-filter-widget-script', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-.php' )));

	} // end register_widget_scripts

	/**
	 * Registers and enqueues widget-specific styles.
	 */
	public function register_widget_styles() {
		wp_register_style( 'taxonomies-filter-widget-styles', plugins_url( ES_PLUGIN_DIR.'/wp/lib/facet-widget/css/widget.css' ) );
		wp_register_style( 'taxonomies-filter-widget-slider-styles', plugins_url( ES_PLUGIN_DIR.'/wp/lib/facet-widget/css/nouislider.fox.css' ) );
		wp_enqueue_style( 'taxonomies-filter-widget-styles' );
		wp_enqueue_style( 'taxonomies-filter-widget-slider-styles' );
	} // end register_widget_styles
    

    /*--------------------------------------------------*/
	/* Other Functions
	/*--------------------------------------------------*/


	/**
	 * Get all registered post types
	 */
	public function get_all_post_types(){

		$args = array(
		  'public'   => true
		); 
		$output = 'objects'; 
		$operator = 'and'; 
		return get_post_types( $args, $output, $operator ); 
	}

	/**
	 * Get all the taxonomies defined in the blog
	 */
	public function get_all_taxonomies(){

		$args = array(
		  'public'   => true
		); 
		$output = 'names'; 
		$operator = 'and'; 
		return get_taxonomies( $args, $output, $operator ); 
	}


	/**
	 * Get all taxonomies registered for a post type
	 */
	public function get_post_type_taxonomies($post_type){

		return get_object_taxonomies($post_type, 'names');
	}

	/**
	 * Check if the taxonomy is valid and is public
	 */
	public function valid_public_taxonomy( $tax_name ) {

		$taxonomy = get_taxonomy( $tax_name );
		if ( $taxonomy && $taxonomy->public && $taxonomy->query_var ){
		    return $taxonomy;
		}
		return false;
	}

	/**
	 * Prints out a taxonomy in the widget's config panel
	 */
	public function admin_print_taxonomy( $taxonomy, $selected_filters, $i, $output ) {
	?>

		<li class="tfw_taxonomy">
			<label>
				<input type="checkbox" name="<?php echo $this->get_field_name('filters'); ?>[<?php echo $i; ?>][name]" value="<?php echo $taxonomy->name; ?>" <?php checked($this->in_array_r($taxonomy->name, $selected_filters)); ?> > 
				<?php echo $taxonomy->label; ?>
			</label>

			<select name="<?php echo $this->get_field_name('filters'); ?>[<?php echo $i; ?>][mode]">
				<?php 
				$output_modes = array('dropdown','multiselect','checkbox','radio');
				foreach ($output_modes as $output_mode) { ?>
					<option value="<?php echo $output_mode; ?>" <?php selected($output_mode == $output); ?> ><?php echo $output_mode; ?></option>
				<?php }	?>
			</select>
		</li>
	
	<?php
	} // end admin_print_taxonomy

	/**
	 * Returns the current term's parent
	 * @param	integer 	term		The id of the current term
	 * @param	object		taxonomy	The taxonomy to which the term belongs
	 * @return 	object      parent_term	The term's parent
	 */

		/**
	 * Handles Ajax processing and returns the HTML element if selected term has childrens
	 */
	public function ajax_drilldown() {

		$taxonomy = esc_attr($_REQUEST['taxonomy']);
		$term = esc_attr($_REQUEST['term']);
		$name = $taxonomy;
		if ($taxonomy == 'category_name') {
			$taxonomy = 'category';
		}
		$current_term = get_term_by( 'slug', $term, $taxonomy );
		$childrens = array();		    
	    if (isset($current_term->term_id)) {
		    $childrens = get_terms ($taxonomy, array(
				'child_of'	=> $current_term->term_id,
				'hide_empty'    => $this->tfw_options['hide_empty']
			));
		}
		if ($childrens){
		    $this->print_dropdown_taxonomy($taxonomy,$current_term->term_id,'',1,$name);
	    }		    
	    die();
	} //ajax_drilldown

	public function get_term_parent ($term, $taxonomy){
		$parent_term = get_term_by( 'id', intval($term->parent) , $taxonomy);
		return $parent_term;
	}

	/**
	 * Walks through given taxonomy parents and call the print function for each one;
	 * Also checks if the form has been already submitted and if so, call the print function
	 * for each child previously selected; 	 	 
	 * @param	string	taxonomy	Name of the taxonomy
	 */	 	

	public function taxonomy_dropdown_walker($taxonomy){   

		if ($taxonomy) { 
				
				$name = fix_taxonomy_name($taxonomy);
				$current_term = get_term_by( 'slug', get_query_var($name) , $taxonomy);
				$selected = get_query_var($name);
				$tax = get_taxonomy($taxonomy);
				echo '<li class="'.$taxonomy.'-section"><label class="taxlabel">'.$tax->label.'</label>'; 
				
				if ($selected && is_taxonomy_hierarchical( $taxonomy ) ){ // if form has been submitted and this taxonomy has been selected							  	  	
					$new_term = $current_term;
				    $has_parents = 1;
				    $output_array = array();
				    $childrens = array();		    
				    if (isset($current_term->term_id)) {
					    $childrens = get_terms ($taxonomy, array(
							'child_of'	=> $current_term->term_id,
							'hide_empty'    => Config::option('hide_empty')
						));
					}
					if ($childrens){
					    $output_array[] = $this->print_dropdown_taxonomy($taxonomy,$current_term->term_id,'',0,$name);
				    }	
					while ($has_parents != 0){
		    		    if ($current_term->parent == 0){
		    		    	$output_array[] = $this->print_dropdown_taxonomy($taxonomy, $current_term->parent, $current_term->slug, 0, $name); //store the dropdown
							$has_parents = 0; 
						} else {
							$output_array[] = $this->print_dropdown_taxonomy($taxonomy, $current_term->parent, $current_term->slug, 0, $name); //store the dropdown
							$parent_term = $this->get_term_parent( $current_term , $taxonomy); // get the closest parent
							$current_term = get_term_by( 'id', $parent_term->term_id , $taxonomy);
							$has_parents = $parent_term->term_id;
						} //end else		
					}
					$output_array = array_reverse($output_array);  //reverse the array with dropdowns since we started from the last child
					foreach ($output_array as $output){
						echo $output;
					} //end foreach
				} else { 
					$this->print_dropdown_taxonomy($taxonomy,0,$selected,1,$name);  // display the dropdown for the top level term without anything selected
				}
				echo '</li>';
		} // end if
	}// end taxonomy_dropdown_walker
	

	/**
	* Prints out the taxonomy or return a string with the dropdown code using the wp_dropdown_categories function
	* @param 	string	taxonomy	Taxonomy name
	* @param	integer	parent		Id of the parent term
	* @param	string	selected	Name of the term that needs to be selected
	* @param	bool	echo		echo html when true; returns string when false				
	* @return	mixed	html|string
	**/
			
	public function print_dropdown_taxonomy($taxonomy, $parent = 0, $selected = '', $echo = 1, $name){      
 		global $NHP_Options;
		if ( taxonomy_exists($taxonomy) ) { 
			$tax = get_taxonomy($taxonomy);
			$select_all = isset($this->filters[$this->number]['select_all']) ? $this->filters[$this->number]['select_all'] : '';
			$args = array(
				'show_option_all'    => $select_all,
				'show_option_none'   => '',
				'orderby'            => 'name', 
				'order'              => 'ASC',
				'show_count'         => $NHP_Options->get('post_count'),
				'hide_empty'         => Config::option('hide_empty'), 
				'child_of'           => $parent,
				'exclude'            => '',
				'echo'               => $echo,
				'selected'           => $selected,
				'hierarchical'       => 1, 
				'name'               => $name,
				'id'                 => $parent ? 'sub_cat_'.$taxonomy : $taxonomy,
				'class'              => $tax->hierarchical ? 'taxonomies-filter-widget-input tax-with-childrens' :'taxonomies-filter-widget-input',
				'depth'              => 1,
				'tab_index'          => 0,
				'taxonomy'           => $taxonomy,
				'hide_if_empty'      => Config::option('hide_empty'),
				'walker'			 => new Walker_TaxonomiesDropdown()
			); 

			if ($echo) {
			    wp_dropdown_categories( $args );
			} else {
				return wp_dropdown_categories( $args );
			}
		} // end if
	} //end print_dropdown_taxonomy



	/**
	* Prints out the taxonomy using multiselect form 
	* @param 	string 	taxonomy 	Name of the taxonomy to be printed out
	**/
	public function print_multiselect_taxonomy($taxonomy){      
 		
 		if ( taxonomy_exists($taxonomy) ) {
 			global $NHP_Options;
 			$tax = get_taxonomy($taxonomy); 
 			$tax_name = fix_taxonomy_name($taxonomy);
 			$elasticsearch = $tax_name.$NHP_Options->get('multiple_relation');
 			$output = '
 				<li class="'.$tax_name.'_li">
 					<label class="taxlabel">'.$tax->label.'</label>
 					<select name="'.$elasticsearch.'[]'.'" class="taxonomies-filter-widget-multiselect" multiple="multiple">';
			$output_list = wp_list_categories(array(
				'walker'   => new Walker_TaxonomiesMultiselect(),
				'name'     => $tax_name,       
				'orderby'  => 'name',
				'order'    => 'ASC',
				'title_li' => '',
				'style'    => 'list',
				'echo'	   => 0,
				'show_count' => $NHP_Options->get('post_count'),
				'hide_empty' => Config::option('hide_empty'),
				'taxonomy' => $taxonomy    
			));
					
			if (trim( $output_list )) {  
				$output .= $output_list.'</select>'.'</li>';
				echo $output;
			}

		} //end if

	} //end print_checkbox_taxonomy

	/**
	* Prints out the taxonomy using checkboxes 
	* @param 	string 	taxonomy 	Name of the taxonomy to be printed out
	**/
	public function print_checkbox_taxonomy($taxonomy){      
 		if ( taxonomy_exists($taxonomy) ) {
 			global $NHP_Options;
 			$tax = get_taxonomy($taxonomy); 
 			$tax_name = fix_taxonomy_name($taxonomy);
 			$output = '
 				<li class="'.$tax_name.'_li">
 					<label class="taxlabel">'.$tax->label.'</label>
 					<ul class="checkboxes_list">';
			$output_list = wp_list_categories(array(
				'walker'   	=> new Walker_TaxonomiesChecklist(),
				'orderby'  	=> 'name',
				'order'    	=> 'ASC',
				'name'		=> "$tax_name", 
				'title_li' 	=> '',
				'style'    	=> 'list',
				'echo'	   	=> 0,
				'show_count' => $NHP_Options->get('post_count'),
				'hide_empty' => Config::option('hide_empty'),
				'taxonomy' 	=> $taxonomy    
			));

			
			if (trim( $output_list )) {  
				$output .= $output_list;
				echo $output;
			}
		} //end if
	} //end print_checkbox_taxonomy

	/**
	* Prints out the taxonomy using radio buttons 
	* @param 	string 	taxonomy 	Name of the taxonomy to be printed out
	**/
	public function print_radio_taxonomy($taxonomy){      
 		
 		if ( taxonomy_exists($taxonomy) ) {
 			global $NHP_Options;
 			$tax = get_taxonomy($taxonomy); 
 			$name = fix_taxonomy_name($taxonomy);  
			$output = '
				<li class="'.$taxonomy.'_li">
					<label class="taxlabel">'.$tax->label.'</label>
					<ul class="radio_list">';
			$output_list = wp_list_categories(array(
				'walker'   => new Walker_TaxonomiesRadio(),
				'name'     => $name,       // name of the input
				'orderby'  => 'name',
				'order'    => 'ASC',
				'show_option_none' => '',
				'title_li' => 0,
				'style'    => 'list',
				'echo'     => 0,
				'show_count' => $NHP_Options->get('post_count'),
				'hide_empty' => Config::option('hide_empty'),
				'taxonomy' => $taxonomy    
			));
			
			if (trim( $output_list )) {
				$output .= $output_list.'</ul></li>';
				echo $output;
			}

		} //end if
	} //end print_radio_taxonomy

	


	/**
	* Recursive search to check if a values exists in an array of arrays 
	**/
	public function in_array_r($needle, $haystack, $strict = false) {
	    foreach ($haystack as $item) {
	        if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && $this->in_array_r($needle, $item, $strict))) {
	            return true;
	        }
	    }
	    return false;
	} // end in_array_r

	/**
	* Cast decimal precision to meta_query type: DECIMAL
	**/
	public function cast_decimal_precision( $array ) {
		$array['where'] = str_replace('DECIMAL','DECIMAL(10,3)',$array['where']);
		return $array;
	}

} // end class


add_action('widgets_init', function(){
	return register_widget("elasticsearch\Taxonomies_Filter_Widget");
});
