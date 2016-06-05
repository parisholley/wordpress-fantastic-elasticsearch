<?php

class FactingSelectedWidget extends \WP_Widget
{

	function __construct()
	{
		// Instantiate the parent object
		parent::__construct(false, 'Faceting: Selected');
	}

	function isremoveable($slug)
	{
		global $wp_query;

		$istag = isset($wp_query->query_vars['tag']) && $slug == $wp_query->query_vars['tag'];
		$iscat = isset($wp_query->query_vars['category_name']) && $slug == $wp_query->query_vars['category_name'];
		$istax = is_tax() && isset($wp_query->queried_object) && $slug == $wp_query->queried_object->slug;
		$isarchive = is_archive() && isset($wp_query->query_vars['post_type']) && $slug == $wp_query->query_vars['post_type'];

		return !($istag || $iscat || $istax || $isarchive);
	}

	function widget($args, $instance)
	{
		global $wp_query;

		$facets = elasticsearch\Faceting::all();

		$async = isset($instance['async']) && $instance['async'];
		$split = isset($instance['splitSpaces']) && $instance['splitSpaces'];

		if ($async) {
			wp_enqueue_script("jquery");
			wp_enqueue_script('elasticsearch', plugins_url('/js/ajax.js', __FILE__), array('jquery'));

			wp_localize_script('elasticsearch', 'esselected', array(
				'showEmpty' => isset($instance['showEmpty']) ? 1 : 0
			));
		}

		$url = null;

		if (is_category() || is_tax()) {
			$url = get_term_link($wp_query->queried_object);
		} elseif (is_tag()) {
			$url = get_tag_link($wp_query->queried_object->term_id);
		} elseif (is_archive()) {
			$url = get_post_type_archive_link($wp_query->queried_object->query_var);
		} elseif (is_search()) {
			$url = home_url('/');
		}

		if ($url == null) {
			return null;
		}

		if ($async) {
			echo '<aside id="facet-selected" class="widget facets facets-selected">';

			echo '<h3 class="widget-title"><span class="widget-title-inner">Your Selections</span></h3>';

			if ($async) {
				echo '<span class="clear"><a href="#" class="clear-inner esclear">Clear All</a></span>';
			}

			echo '<ul>';

			if (is_search()) {
				if ($split && strpos($wp_query->query_vars['s'], ' ') !== false) {
					$split = explode(' ', trim($wp_query->query_vars['s']));

					foreach ($split as $term) {
						$term = trim($term);

						if ($term) {
							echo '<li class="term removable" data-term="' . $term . '"><a href="#search-term-' . $term . '">Search: ' . $term . '</a></li>';
						}
					}
				} else {
					echo '<li data-term="' . $term . '">Search: ' . $wp_query->query_vars['s'] . '</li>';
				}
			}

			foreach ($facets as $type => $facet) {
				$name = $type;

				if (taxonomy_exists($type)) {
					$name = get_taxonomy($type)->labels->singular_name;
				}

				if (isset($facet['available'])) {
					foreach ($facet['available'] as $option) {
						$isremovable = $this->isremoveable($option['slug']);

						echo '<li id="facet-' . $type . '-' . $option['slug'] . '-selected"';

						if ($isremovable) {
							echo ' style="display:none" class="facet-item removable">';
							echo '<a href="#facet-' . $type . '-' . $option['slug'] . '">' . ($name == 'post_type' ? 'Content Type' : $name) . ': ' . $option['name'] . '</a>';
						} else {
							echo 'class="facet-item">' . ($name == 'post_type' ? 'Content Type' : $name) . ': ' . $option['name'];
						}

						echo '</li>';
					}
				}
			}

			echo '</ul>';

			echo '</aside>';
		} else {
			foreach ($facets as $type => $facet) {
				if (count($facet['selected']) > 0) {
					$name = $type;

					if (taxonomy_exists($type)) {
						$name = get_taxonomy($type)->label;
					}

					echo '<aside id="facet-' . $type . '-selected" class="widget facets facets-selected">';

					echo '<h3 class="widget-title"><span class="widget-title-inner">' . $name . '</span></h3>';

					echo '<ul>';

					foreach ($facet['selected'] as $option) {
						$url = elasticsearch\Faceting::urlRemove($url, $type, $option['slug']);

						$isremovable = $this->isremoveable($option['slug']);

						echo '<li id="facet-' . $type . '-' . $option['slug'] . '" class="facet-item">';

						if ($isremovable) {
							echo '<a href="' . $url . '">' . $option['name'] . '</a>';
						} else {
							echo $name . ':' . $option['name'];
						}

						echo '</li>';
					}

					echo '</ul>';

					echo '</aside>';
				}
			}
		}
	}

	function update($new_instance, $old_instance)
	{
		return $new_instance;
	}

	function form($instance)
	{
		$defaults = array(
			'async' => false,
			'showEmpty' => false,
			'splitSpaces' => false,
		);

		$instance = array_merge($defaults, $instance);
		?>
		<p>
			<input class="checkbox" type="checkbox" <?php checked(isset($instance['async']) ? $instance['async'] : false, true); ?>
				   id="<?php echo $this->get_field_id('async'); ?>" name="<?php echo $this->get_field_name('async'); ?>" value="1"/>
			<label for="<?php echo $this->get_field_id('async'); ?>">Update selected items asynchronously (requires faceting options
				widget)</label>
		</p>
		<p>
			<input class="checkbox" type="checkbox" <?php checked(isset($instance['showEmpty']) ? $instance['showEmpty'] : false, true); ?>
				   id="<?php echo $this->get_field_id('showEmpty'); ?>" name="<?php echo $this->get_field_name('showEmpty'); ?>" value="1"/>
			<label for="<?php echo $this->get_field_id('showEmpty'); ?>">Show empty message and hide available options when all facets are
				selected.</label>
		</p>
		<p>
			<input class="checkbox"
				   type="checkbox" <?php checked(isset($instance['splitSpaces']) ? $instance['splitSpaces'] : false, true); ?>
				   id="<?php echo $this->get_field_id('splitSpaces'); ?>" name="<?php echo $this->get_field_name('splitSpaces'); ?>"
				   value="1"/>
			<label for="<?php echo $this->get_field_id('splitSpaces'); ?>">Split search query by spaces so user can remove words
				individually.</label>
		</p>
		<?php
	}
}

add_action('widgets_init', function () {
	register_widget('FactingSelectedWidget');
});
?>
