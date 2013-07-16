<div>
<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:','taxonomies-filter-widget' ); ?></label>
<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>

<p><label for="<?php echo $this->get_field_id('tfw_post_type'); ?>"><?php _e( 'Select post type:','taxonomies-filter-widget' ); ?></label>
<select name="<?php echo $this->get_field_name('tfw_post_type'); ?>" class="tfw_post_type">
<?php foreach ($this->get_all_post_types() as $post_type) { ?>
	<option value="<?php echo $post_type->name; ?>" <?php selected($tfw_post_type == $post_type->name); ?> ><?php echo $post_type->labels->singular_name; ?></option>
<?php } ?>
</select>
</p>


<p> 
<?php 
	!empty($all_filters) ? _e( 'Taxonomies for: ', 'taxonomies-filter-widget' ) : _e( 'No taxonomies for: ', 'taxonomies-filter-widget' ); 
	$obj = get_post_type_object($tfw_post_type);
	echo $obj->labels->name;  
?> 
</p>

	<ul class="taxonomies-admin-list">
	<?php 
		$i = 0;
		foreach($all_filters as $filter ){
			if ( $taxonomy = $this->valid_public_taxonomy($filter['name']) ) {
				$this->admin_print_taxonomy( $taxonomy , $selected_filters, $i, $filter['mode'] );
			} 
			$i++; 
		} // endforeach 
	?> 

	</ul>

<p><label for="<?php echo $this->get_field_id('search_box_label'); ?>"><?php _e( 'Search box label:', 'taxonomies-filter-widget' ); ?></label>
<input class="widefat" id="<?php echo $this->get_field_id('search_box_label'); ?>" name="<?php echo $this->get_field_name('search_box_label'); ?>" type="text" value="<?php echo $search_box_label; ?>" />
</p>

<p><label for="<?php echo $this->get_field_id('reset_button_label'); ?>"><?php _e( 'Reset button label:', 'taxonomies-filter-widget' ); ?></label>
<input class="widefat" id="<?php echo $this->get_field_id('reset_button_label'); ?>" name="<?php echo $this->get_field_name('reset_button_label'); ?>" type="text" value="<?php echo $reset_button_label; ?>" />
</p>

<p><label for="<?php echo $this->get_field_id('submit'); ?>"><?php _e( 'Submit button label:', 'taxonomies-filter-widget' ); ?></label>
<input class="widefat" id="<?php echo $this->get_field_id('submit'); ?>" name="<?php echo $this->get_field_name('submit'); ?>" type="text" value="<?php echo $submit; ?>" />
</p>

<p><label for="<?php echo $this->get_field_id('select_all'); ?>"><?php _e( 'Select all option:', 'taxonomies-filter-widget' ); ?></label>
<input class="widefat" id="<?php echo $this->get_field_id('select_all'); ?>" name="<?php echo $this->get_field_name('select_all'); ?>" type="text" value="<?php echo $select_all; ?>" />
</p>

</div>