<?php
namespace elasticsearch;

require_once( plugin_dir_path(__FILE__) . '/address-geocoder/address-geocoder.php' );

class Map_Widget extends \WP_Widget {

	/*--------------------------------------------------*/
	/* Constructor
	/*--------------------------------------------------*/   

	/**
	 * Specifies the classname and description, instantiates the widget, 
	 * loads localization files, and includes necessary stylesheets and JavaScript.
	 */
	 
	public static $map_options;
	public $map_filters;

	public function __construct() {  

		parent::__construct(
			'map-widget',
			__( 'Elasticsearch Map', 'map-widget' ),
			array(
				'classname'		=>	'map-widget',
				'description'	=>	__( 'Geo-facets using Google Maps API.', 'map-widget' )
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
		$title 				 = apply_filters('widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base);

		echo $before_widget;
		include( plugin_dir_path( __FILE__ ) . '/views/widget.php' );
		echo $after_widget;
	} // end widget
	
	/**
	 * Generates the administration form for the widget.
	 *
	 * @param	array	instance	The array of keys and values for the widget.
	 */
	public function form( $instance ) {
	
    	// Extracting definded values and defining default values for variables

	    $instance = wp_parse_args( (array) $instance, array( 
			'title' => '', 
		));

		$title 				= esc_attr( $instance['title'] );

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

		delete_option( 'widget_map-widget' );
		delete_option( 'tfw_options' );
	} // end deactivate
	

	/**
	 * Registers and enqueues admin-specific JavaScript and CSS only on widgets page.
	 */	
	public function register_admin_scripts_styles($hook) {

		if( 'widgets.php' != $hook )
        	return;
        wp_enqueue_style( 'taxonomies-filter-widget-admin-styles', plugins_url( ES_PLUGIN_DIR.'/lib/facet-widget/css/admin.css' ) );
		wp_enqueue_script( 'taxonomies-filter-widget-admin-script', plugins_url( ES_PLUGIN_DIR.'/lib/facet-widget/js/admin.js' ), array('jquery'), false, true );
		
	} // end register_admin_scripts

	/**
	 * Registers and enqueues widget-specific styles.
	 */
	public function register_widget_styles() {
		wp_enqueue_style( 'map-widget-slider-style1', plugins_url( ES_PLUGIN_DIR.'/lib/map-widget/css/bootstrap.css' ) );
		wp_enqueue_style( 'map-widget-slider-style2', plugins_url( ES_PLUGIN_DIR.'/lib/map-widget/css/slider.css' ) );
		wp_enqueue_style( 'map-widget-slider-style3', plugins_url( ES_PLUGIN_DIR.'/lib/map-widget/css/app.css' ) );
	} // end register_widget_styles
	
	/**
	 * Registers and enqueues widget-specific scripts.
	 */
	public function register_widget_scripts() {
		global $NHP_Options;
		$elasticsearchUrl = $NHP_Options->get('server_url');
		wp_enqueue_script( 'map-widget-script4', plugins_url( ES_PLUGIN_DIR.'/lib/map-widget/js/jquery.js' ));
		wp_enqueue_script( 'map-widget-script2', plugins_url( ES_PLUGIN_DIR.'/lib/map-widget/js/bootstrap-slider.js' ));
		wp_enqueue_script( 'map-widget-script3', plugins_url( ES_PLUGIN_DIR.'/lib/map-widget/js/handlebars-1.0.0-rc.3.js' ));
		wp_enqueue_script( 'map-widget-script6', 'http://maps.googleapis.com/maps/api/js?key='.$NHP_Options->get('map_api_key').'&sensor=false' );
		wp_enqueue_script( 'map-widget-script1', plugins_url( ES_PLUGIN_DIR.'/lib/map-widget/js/qbox-map.js' ), array('map-widget-script4'), false, false );
		wp_localize_script( 'map-widget-script1', 'elasticsearchUrl', $elasticsearchUrl);

	} // end register_widget_scripts

    /*--------------------------------------------------*/
	/* Other Functions
	/*--------------------------------------------------*/	

} // end class


add_action('widgets_init', function(){
	return register_widget("elasticsearch\Map_Widget");
});
