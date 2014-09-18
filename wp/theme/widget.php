<?php
class FacetingOptionsWidget extends \WP_Widget {

	function __construct() {
		// Instantiate the parent object
		parent::__construct( false, 'Faceting: Options' );
	}

	function widget( $args, $instance ) {
		global $wp_query;
		
		$facets = elasticsearch\Faceting::all();

		$prep = array();

		$url = null;

		if(is_category() || is_tax()){
			$url = get_term_link($wp_query->queried_object);
		}elseif(is_tag()){
			$url = get_tag_link($wp_query->queried_object->term_id);				
		}elseif(is_archive()){
			$url = get_post_type_archive_link($wp_query->queried_object->query_var);
		}elseif(is_search()){
			$url = home_url('/');
		}

		foreach($facets as $type => $facet){
			if($facet['total'] > 0){
				foreach($facet['available'] as $option){
					if($option['count'] != $wp_query->post_count){
						if(!isset($prep[$type])){
							$name = $type;

							if(taxonomy_exists($type)){
								$name = get_taxonomy($type)->label;
							}

							$prep[$type] = array(
								'type' => $type,
								'name' => $name,
								'avail' => array()
							);
						}

						$prep[$type]['avail'][] = array(
							'url' => elasticsearch\Faceting::urlAdd($url, $type, $option['slug']),
							'option' => $option
						);
					}
				}
			}
		}

		if(count($prep) > 0){
			foreach($prep as $type => $settings){
				echo '<aside id="facet-' . $type . '-available" class="widget facets facets-available">';

				echo '<h3 class="widget-title">' . $settings['name'] . '</h3>';

				echo '<ul>';

				foreach($settings['avail'] as $avail){
					echo '<li id="facet-' . $type . '-' . $avail['option']['slug'] . '" class="facet-item">';
					echo '<a href="' . $avail['url'] . '">' . $avail['option']['name'] . ' (' . $avail['option']['count'] . ')</a>';
					echo '</li>';
				}

				echo '</ul>';

				echo '</aside>';
			}
		}
	}

	function update( $new_instance, $old_instance ) {
		// Save widget options
	}

	function form( $instance ) {
		// Output admin widget options form
	}
}

class FactingSelectedWidget extends \WP_Widget {

	function __construct() {
		// Instantiate the parent object
		parent::__construct( false, 'Faceting: Selected' );
	}

	function widget( $args, $instance ) {
		global $wp_query;
		
		$facets = elasticsearch\Faceting::all();

		$url = null;
		
		if(is_category() || is_tax()){
			$url = get_term_link($wp_query->queried_object);
		}elseif(is_tag()){
			$url = get_tag_link($wp_query->queried_object->term_id);				
		}elseif(is_archive()){
			$url = get_post_type_archive_link($wp_query->queried_object->query_var);
		}elseif(is_search()){
			$url = home_url('/');
		}

		foreach($facets as $type => $facet){
			if(count($facet['selected']) > 0){
				$name = $type;

				if(taxonomy_exists($type)){
					$name = get_taxonomy($type)->label;
				}

				echo '<aside id="facet-' . $type . '-selected" class="widget facets facets-selected">';

				echo '<h3 class="widget-title">' . $name . '</h3>';

				echo '<ul>';

				foreach($facet['selected'] as $option){
					$url = elasticsearch\Faceting::urlRemove($url , $type, $option['slug']);

					echo '<li id="facet-' . $type . '-' . $option['slug'] . '" class="facet-item">';
					echo '<a href="' . $url . '">' . $option['name'] . '</a>';
					echo '</li>';
				}

				echo '</ul>';

				echo '</aside>';
			}
		}
	}

	function update( $new_instance, $old_instance ) {
		// Save widget options
	}

	function form( $instance ) {
		// Output admin widget options form
	}
}

add_action( 'widgets_init', function() {
	register_widget( 'FacetingOptionsWidget' );
	register_widget( 'FactingSelectedWidget' );
});

?>