<?php
namespace elasticsearch;

require_once( plugin_dir_path(__FILE__) . 'wp-charts/wordpress_charts_js.php' );

class Charts_Widget extends \WP_Widget {

	/*--------------------------------------------------*/
	/* Constructor
	/*--------------------------------------------------*/   

	/**
	 * Specifies the classname and description, instantiates the widget, 
	 * loads localization files, and includes necessary stylesheets and JavaScript.
	 */
	 
	public static $charts_options;
	public $filters;

	public function __construct() {  

		parent::__construct(
			'charts-widget',
			__( 'Elasticsearch Charts', 'charts-widget' ),
			array(
				'classname'		=>	'charts-widget',
				'description'	=>	__( 'Create charts with Elasticsearch data.', 'charts-widget' )
			)
		);
	
		// load plugin text domain
		add_action( 'init', array( $this, 'widget_textdomain' ) );
		
		// Hooks fired when the Widget is activated and deactivated
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

		// Register admin styles and scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_scripts_styles' ) );
	
		// Register site styles and scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'register_widget_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_widget_styles' ) );
		
	} // end constructor     

	/*--------------------------------------------------*/
	/* Widget API Functions
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
		$charts_post_type 	 = isset( $instance['charts_post_type'] ) ? $instance['charts_post_type'] : 'post';
		// labels
		$submit 			 = empty( $instance['labels'] ) ? '' : $instance['labels'];
		$select_all  		 = empty( $instance['colors'] ) ? '' : $instance['colors'];
       	$search_box_label 	 = empty( $instance['fillopacity'] ) ? '' : $instance['fillopacity'];
       	$reset_button_label  = empty( $instance['pointstrokecolor'] ) ? '' : $instance['pointstrokecolor'];

		
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
		$instance['charts_post_type'] 	= isset( $new_instance['charts_post_type'] ) ? $new_instance['charts_post_type'] : 'post';

		//labels
		$instance['labels'] 			= strip_tags($new_instance['labels']);
		$instance['colors'] 			= strip_tags($new_instance['colors']);
		$instance['fillopacity'] 		= strip_tags($new_instance['fillopacity']);
		$instance['pointstrokecolor']   = strip_tags($new_instance['pointstrokecolor']);

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
			'charts_post_type' => 'post',
			'labels' => 'Labels',
			'colors' => 'colors',
			'fillopacity' => 'fillopacity',
			'pointstrokecolor' => 'pointstrokecolor'
		));

		$title 				= esc_attr( $instance['title'] );
		$selected_filters	= isset( $instance['selected_filters'] ) ? $instance['selected_filters'] : array();
		$charts_post_type 	= isset( $instance['charts_post_type'] ) ? $instance['charts_post_type'] : 'post';

		//labels
		$submit 			= esc_attr( $instance['labels'] );
		$select_all 		= esc_attr( $instance['colors'] );
		$search_box_label 	= esc_attr( $instance['fillopacity'] );
		$reset_button_label = esc_attr( $instance['pointstrokecolor'] );


		// Get already selected filters and put them at the top
		$all_filters = $selected_filters;

		// Append all other taxonomies
		foreach ( $this->get_post_type_taxonomies($charts_post_type) as $taxonomy ) {
			if ( !$this->in_array_r( $taxonomy, $selected_filters ) )
				$all_filters[] = array('name' => $taxonomy,'mode'=>'pie');
		}

		// Display the admin form
		include( plugin_dir_path(__FILE__) . '/views/admin.php' );	
		
	} // end form


	/*--------------------------------------------------*/
	/* Widget Specific Functions
	/*--------------------------------------------------*/
	
	/**
	 * Loads the Widget's text domain for localization and translation.
	 */
	public function widget_textdomain() {
	
		load_plugin_textdomain( 'taxonomies-filter-widget', false, plugin_dir_path( __FILE__ ) . '/lang/' );
		
	} // end widget_textdomain
	
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

		delete_option( 'widget_charts-widget' );
		delete_option( 'charts_options' );
	} // end deactivate
	

	/**
	 * Registers and enqueues admin-specific JavaScript and CSS only on widgets page.
	 */	
	public function register_admin_scripts_styles($hook) {

		if( 'widgets.php' != $hook )
        	return;
		wp_enqueue_script( 'charts-widget-admin-script', plugins_url( ES_PLUGIN_DIR.'/lib/charts-widget/js/admin.js' ), array('jquery'), false, true );
		wp_enqueue_style( 'charts-widget-admin-styles', plugins_url( ES_PLUGIN_DIR.'/lib/charts-widget/css/admin.css' ) );
		
	} // end register_admin_scripts
	
	/**
	 * Registers and enqueues widget-specific scripts.
	 */
	public function register_widget_scripts() {
		global $NHP_Options;
		wp_enqueue_script('jquery');
		wp_localize_script( 'charts-widget-script', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-.php' )));

	} // end register_widget_scripts

	/**
	 * Registers and enqueues widget-specific styles.
	 */
	public function register_widget_styles() {
		wp_register_style( 'charts-widget-styles', plugins_url( ES_PLUGIN_DIR.'/lib/charts-widget/css/widget.css' ) );
		wp_register_style( 'charts-widget-slider-styles', plugins_url( ES_PLUGIN_DIR.'/lib/charts-widgetcss/nouislider.fox.css' ) );
		wp_enqueue_style( 'charts-widget-styles' );
		wp_enqueue_style( 'charts-widget-slider-styles' );
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

		<li class="charts_taxonomy">
			<label>
				<input type="checkbox" name="<?php echo $this->get_field_name('filters'); ?>[<?php echo $i; ?>][name]" value="<?php echo $taxonomy->name; ?>" <?php checked($this->in_array_r($taxonomy->name, $selected_filters)); ?> > 
				<?php echo $taxonomy->label; ?>
			</label>

			<select name="<?php echo $this->get_field_name('filters'); ?>[<?php echo $i; ?>][mode]">
				<?php 
				$output_modes = array('pie', 'doughnut', 'radar', 'polararea', 'bar', 'line');
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
	* Prints out the taxonomy or return a string with the dropdown code using the wp_dropdown_categories function
	* @param 	string	taxonomy	Taxonomy name
	* @param	integer	parent		Id of the parent term
	* @param	string	selected	Name of the term that needs to be selected
	* @param	bool	echo		echo html when true; returns string when false				
	* @return	mixed	html|string
	**/
			
	public function print_pie($taxonomy, $name){      
 		global $NHP_Options;
		if ( taxonomy_exists($taxonomy) ) { 
			$tax = get_taxonomy($taxonomy);
		 echo do_shortcode('[wp_charts title="mypie" type="pie" data="10,32,50,25,5" colors = "#69D2E7,#E0E4CC,#F38630,#96CE7F,#CEBC17,#CE4264" fillopacity = "0.7"]');
		} // end if
	} //end print_dropdown_taxonomy

	public function print_doughnut ($taxonomy, $name){      
 		global $NHP_Options;
		if ( taxonomy_exists($taxonomy) ) { 
			$tax = get_taxonomy($taxonomy);
			 echo do_shortcode('[wp_charts title="mydough" type="doughnut" data="30,10,55,25,15" colors = "#69D2E7,#E0E4CC,#F38630,#96CE7F,#CEBC17,#CE4264" fillopacity = "0.7"]');
		} // end if
	} //end print_dropdown_taxonomy

	public function print_radar ($taxonomy, $name){      
 		global $NHP_Options;
		if ( taxonomy_exists($taxonomy) ) { 
			$tax = get_taxonomy($taxonomy);
			 echo do_shortcode('[wp_charts title="mypolar" type="polarArea" data="40,32,5,25,50" labels="one,two,three,four,five" colors = "#69D2E7,#E0E4CC,#F38630,#96CE7F,#CEBC17,#CE4264" fillopacity = "0.7"]');
		} // end if
	} //end print_dropdown_taxonomy

	public function print_polararea ($taxonomy, $name){      
 		global $NHP_Options;
		if ( taxonomy_exists($taxonomy) ) { 
			$tax = get_taxonomy($taxonomy);
			 echo do_shortcode('[wp_charts title="barchart" type="bar" datasets="40,32,50,35,5 next 20,25,45,42 next 40,43, 61,50 next 33,15,40,22" labels="one,two,three,four" colors = "#69D2E7,#E0E4CC,#F38630,#96CE7F,#CEBC17,#CE4264" fillopacity = "0.7" pointstrokecolor = "#FFFFFF"]');
		} // end if
	} //end print_dropdown_taxonomy

	public function print_bar ($taxonomy, $name){      
 		global $NHP_Options;
		if ( taxonomy_exists($taxonomy) ) { 
			$tax = get_taxonomy($taxonomy);
			 echo do_shortcode('[wp_charts title="linechart" type="line" datasets="40,43,61,50 next 33,15,40,22" labels="one,two,three,four" colors = "#69D2E7,#E0E4CC,#F38630,#96CE7F,#CEBC17,#CE4264" fillopacity = "0.7"]');
		} // end if
	} //end print_dropdown_taxonomy

	public function print_line($taxonomy, $name){      
 		global $NHP_Options;
		if ( taxonomy_exists($taxonomy) ) { 
			$tax = get_taxonomy($taxonomy);
			echo do_shortcode('[wp_charts title="radarchart" type="radar" datasets="20,22,40,25,55 next 15,20,30,40,35" labels="one,two,three,four,five" colors = "#69D2E7,#E0E4CC,#F38630,#96CE7F,#CEBC17,#CE4264" fillopacity = "0.7" pointstrokecolor = "#FFFFFF"]');
		} // end if
	} //end print_dropdown_taxonomy

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

} // end class


add_action('widgets_init', function(){
	return register_widget("elasticsearch\Charts_Widget");
});
