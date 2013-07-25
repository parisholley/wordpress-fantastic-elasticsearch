<?php

// Metabox

add_action( 'load-post.php', 'martygeocoder_admin_init' );
add_action( 'load-post-new.php', 'martygeocoder_admin_init' );

function martygeocoder_admin_init() {
	global $NHP_Options;
	wp_register_script( 'googlemaps', 'http://maps.googleapis.com/maps/api/js?key='.$NHP_Options->get('map_api_key').'&sensor=false' );
	wp_enqueue_script( 'googlemaps' );

	wp_register_script( 'marty_geocode_js', plugins_url('/address-geocoder.js', __FILE__) );
	wp_enqueue_script( 'marty_geocode_js' );

	add_action( 'add_meta_boxes', 'martygeocoder_addboxes' );
	add_action( 'save_post', 'martygeocoder_save', 10, 2 );
}

function martygeocoder_addboxes($postType) {
	global $NHP_Options;

	$types=$NHP_Options->get('map_post_types');
	if(in_array($postType, $types)){
		add_meta_box('martygeocoder', 'Geocoder', 'martygeocoder_setup', $postType, 'normal','high');
	}
}

function martygeocoder_setup( $object, $box ) { 

	wp_nonce_field( basename( __FILE__ ), 'martygeocoder_nonce' ); ?>

	<div style="overflow: hidden; width: 100%;">
	<div id="geocodepreview" style="float: right; width: 200px; height: 140px; border: 1px solid #DFDFDF;"></div>

	<div style="margin-right: 215px">

	<p><label for="martygeocoderaddress">Address</label><br />
		<input class="widefat" type="text" name="martygeocoderaddress" id="martygeocoderaddress" value="<?php echo esc_attr( get_post_meta( $object->ID, 'martygeocoderaddress', true ) ); ?>" size="30" /></p>

	<p><label for="latlong">Lat/Lng</label><br />
			<input class="widefat" type="text" name="latlong" id="latlong" value="<?php echo esc_attr( get_post_meta( $object->ID, 'latlong', true ) ); ?>" size="30" /></p>

	<p><a id="geocode" class="button">Geocode Address</a></p>


	</div>
	</div>

<?php }

add_action( 'save_post', 'martygeocoder_save', 10, 2 );

function martygeocoder_save( $post_id, $post ) {

	if ( !isset( $_POST['martygeocoder_nonce'] ) || !wp_verify_nonce( $_POST['martygeocoder_nonce'], basename( __FILE__ ) ) ) {
		return $post_id;
	}

	$post_type = get_post_type_object( $post->post_type );

	if ( !current_user_can( $post_type->cap->edit_post, $post_id ) ) {
		return $post_id;
	}
		

	// Address
	$new_address_value = ( isset( $_POST['martygeocoderaddress'] ) ? sanitize_text_field( $_POST['martygeocoderaddress'] ) : '' );
	$address_key = 'martygeocoderaddress';

	$address_value = get_post_meta( $post_id, $address_key, true );

	if ( $new_address_value && '' == $address_value ) {
		add_post_meta( $post_id, $address_key, $new_address_value, true );
	}

	elseif ( $new_address_value && $new_address_value != $address_value ) {
		update_post_meta( $post_id, $address_key, $new_address_value );
	}

	elseif ( '' == $new_address_value && $address_value ) {
		delete_post_meta( $post_id, $address_key, $address_value );
	}

	// Latlong
	$pre_latlng_value = ( isset( $_POST['latlong'] ) ? sanitize_text_field( $_POST['latlong'] ) : '' );
	$new_latlng_value = str_replace('(', '',  str_replace(')', '',$pre_latlng_value ));
	$latlng_key = 'latlong';

	$latlng_value = get_post_meta( $post_id, $latlng_key, true );

	if ( $new_latlng_value && '' == $latlng_value )
		add_post_meta( $post_id, $latlng_key, $new_latlng_value, true );

	elseif ( $new_latlng_value && $new_latlng_value != $latlng_value )
		update_post_meta( $post_id, $latlng_key, $new_latlng_value );

	elseif ( '' == $new_latlng_value && $latlng_value )
		delete_post_meta( $post_id, $latlng_key, $latlng_value );
	
	// Lat
	$pre_lat_value = explode(',', $new_latlng_value);
	$new_lat_value = $pre_lat_value[0];

	$lat_value = get_post_meta( $post_id, 'location', true );

	if ( $new_lat_value && '' == $lat_value )
		add_post_meta( $post_id, 'location', array('lat' => $new_lat_value), true );

	elseif ( $new_lat_value && $new_lat_value != $lat_value )
		update_post_meta( $post_id, 'location', array('lat' => $new_lat_value) );

	elseif ( '' == $new_lat_value && $lat_value )
		delete_post_meta( $post_id, 'location', array('lat' => $new_lat_value) );

	// Lon
	$new_lon_value = $pre_lat_value[1];
	
	$lon_value = get_post_meta( $post_id, 'location', true );

	if ( $new_lon_value && '' == $lon_value )
		add_post_meta( $post_id, 'location', array('lon' => $new_lon_value), true );

	elseif ( $new_lon_value && $new_lon_value != $lon_value )
		update_post_meta( $post_id, 'location', array('lon' => $new_lon_value) );

	elseif ( '' == $new_lon_value && $lon_value )
		delete_post_meta( $post_id, 'location', array('lon' => $new_lon_value) );
}	

// Front end

function get_geocode_latlng($postid) {
	$martygeocoder = get_post_meta($postid, 'latlong', true);	
	return $martygeocoder;
}

function get_geocode_address($postid) {
	$martygeocoder = get_post_meta($postid, 'martygeocoderaddress', true);	
	return $martygeocoder;
}

 ?>