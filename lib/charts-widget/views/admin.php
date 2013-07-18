<div>
<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:','taxonomies-filter-widget' ); ?></label>
<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>

<p><label for="<?php echo $this->get_field_id('charts_post_type'); ?>"><?php _e( 'Select post type:','charts-widget' ); ?></label>
<select name="<?php echo $this->get_field_name('charts_post_type'); ?>" class="charts_post_type">
<?php foreach ($this->get_all_post_types() as $post_type) { ?>
	<option value="<?php echo $post_type->name; ?>" <?php selected($charts_post_type == $post_type->name); ?> ><?php echo $post_type->labels->singular_name; ?></option>
<?php } ?>
</select>
</p>


<p> 
<?php 
	!empty($all_filters) ? _e( 'Taxonomies for: ', 'charts-widget' ) : _e( 'No taxonomies for: ', 'charts-widget' ); 
	$obj = get_post_type_object($charts_post_type);
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

<p><label for="<?php echo $this->get_field_id('labels'); ?>"><?php _e( 'Labels for bar, line or polararea charts:', 'charts-widget' ); ?></label>
<input class="widefat" id="<?php echo $this->get_field_id('labels'); ?>" name="<?php echo $this->get_field_name('labels'); ?>" type="text" value="<?php echo $labels; ?>" />
</p>

<p><label for="<?php echo $this->get_field_id('colors'); ?>"><?php _e( 'Comma-separated hexadecimals (same number as number of taxonomies) for colors:', 'charts-widget' ); ?></label>
<input class="widefat" id="<?php echo $this->get_field_id('colors'); ?>" name="<?php echo $this->get_field_name('colors'); ?>" type="text" value="<?php echo $colors; ?>" />
</p>

<p><label for="<?php echo $this->get_field_id('fillopacity'); ?>"><?php _e( 'Decimal between 0 and 1 that controls color fill opacity:', 'charts-widget' ); ?></label>
<input class="widefat" id="<?php echo $this->get_field_id('fillopacity'); ?>" name="<?php echo $this->get_field_name('fillopacity'); ?>" type="text" value="<?php echo $fillopacity; ?>" />
</p>

<p><label for="<?php echo $this->get_field_id('pointstrokecolor'); ?>"><?php _e( 'Hexadecimal for chart point stroke color:', 'charts-widget' ); ?></label>
<input class="widefat" id="<?php echo $this->get_field_id('pointstrokecolor'); ?>" name="<?php echo $this->get_field_name('pointstrokecolor'); ?>" type="text" value="<?php echo $pointstrokecolor; ?>" />
</p>

</div>