<div>
<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:','charts-widget' ); ?></label>
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

<p><label for="<?php echo $this->get_field_id('animation'); ?>"><?php _e('Animation?', 'charts-widget' ); ?></label>
<input class="checkbox" type="checkbox" <?php echo $animation; ?> id="<?php echo $this->get_field_id('animation'); ?>" name="<?php echo $this->get_field_name('animation'); ?>" /> 
</p>

<p><label for="<?php echo $this->get_field_id('show_charts_count'); ?>"><?php _e('Display facet counts beside labels?', 'charts-widget' ); ?></label>
<input class="checkbox" type="checkbox" <?php echo $show_charts_count; ?> id="<?php echo $this->get_field_id('show_charts_count'); ?>" name="<?php echo $this->get_field_name('show_charts_count'); ?>" /> 
</p>

<p><label for="<?php echo $this->get_field_id('width'); ?>"><?php _e( 'Chart width percentage:', 'charts-widget' ); ?></label>
<input class="widefat" id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" type="text" value="<?php echo $width; ?>" />
</p>

<p><label for="<?php echo $this->get_field_id('colors'); ?>"><?php _e( 'Comma-separated color values:', 'charts-widget' ); ?></label>
<input class="widefat" id="<?php echo $this->get_field_id('colors'); ?>" name="<?php echo $this->get_field_name('colors'); ?>" type="text" value="<?php echo $colors; ?>" />
</p>

<p><label for="<?php echo $this->get_field_id('fillopacity'); ?>"><?php _e( 'Decimal controlling color fill opacity:', 'charts-widget' ); ?></label>
<input class="widefat" id="<?php echo $this->get_field_id('fillopacity'); ?>" name="<?php echo $this->get_field_name('fillopacity'); ?>" type="number" value="<?php echo $fillopacity; ?>" />
</p>

<p><label for="<?php echo $this->get_field_id('pointstrokecolor'); ?>"><?php _e( 'Color value for chart point stroke color:', 'charts-widget' ); ?></label>
<input class="widefat" id="<?php echo $this->get_field_id('pointstrokecolor'); ?>" name="<?php echo $this->get_field_name('pointstrokecolor'); ?>" type="text" value="<?php echo $pointstrokecolor; ?>" />
</p>

<p><label for="<?php echo $this->get_field_id('scaleSteps'); ?>"><?php _e( 'Number of scale steps:', 'charts-widget' ); ?></label>
<input class="widefat" id="<?php echo $this->get_field_id('scaleSteps'); ?>" name="<?php echo $this->get_field_name('scaleSteps'); ?>" type="number" value="<?php echo $scaleSteps; ?>" />
</p>

<p><label for="<?php echo $this->get_field_id('scaleStepWidth'); ?>"><?php _e( 'Scale steps width:', 'charts-widget' ); ?></label>
<input class="widefat" id="<?php echo $this->get_field_id('scaleStepWidth'); ?>" name="<?php echo $this->get_field_name('scaleStepWidth'); ?>" type="number" value="<?php echo $scaleStepWidth; ?>" />
</p>

<p><label for="<?php echo $this->get_field_id('scaleStartValue'); ?>"><?php _e( 'Scale start value:', 'charts-widget' ); ?></label>
<input class="widefat" id="<?php echo $this->get_field_id('scaleStartValue'); ?>" name="<?php echo $this->get_field_name('scaleStartValue'); ?>" type="number" value="<?php echo $scaleStartValue; ?>" />
</p>

</div>