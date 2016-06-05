<?php

class FacetingOptionsWidget extends \WP_Widget
{

	function __construct()
	{
		// Instantiate the parent object
		parent::__construct(false, 'Faceting: Options');
	}

	function widget($args, $instance)
	{
		global $wp_query;

		$async = isset($instance['async']) && $instance['async'] && isset($instance['asyncReplace']);
		$offset = isset($instance['cssOffset']) ? $instance['cssOffset'] : null;

		if ($async) {
			wp_enqueue_script("jquery");
			wp_enqueue_script('elasticsearch', plugins_url('/js/ajax.js', __FILE__), array('jquery'));

			elasticsearch\Theme::setSelector($instance['asyncReplace']);

			wp_localize_script('elasticsearch', 'esfaceting', array(
				'replace' => $instance['asyncReplace'],
				'offset' => $offset
			));
		}

		$facets = elasticsearch\Faceting::all();

		$prep = array();

		$url = null;
		$selected = null;

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

		foreach ($facets as $type => $facet) {
			if ($facet['total'] > 0 || $async) {
				if (isset($facet['available'])) {
					foreach ($facet['available'] as $option) {
						if ($option['count'] != $wp_query->found_posts) {
							if (!isset($prep[$type])) {
								$name = $type;

								if (taxonomy_exists($type)) {
									$name = get_taxonomy($type)->label;
								}

								$prep[$type] = array(
									'type' => $type,
									'name' => $name,
									'avail' => array(),
									'show' => false
								);
							}

							$prep[$type]['show'] = true;
							$prep[$type]['avail'][] = array(
								'url' => elasticsearch\Faceting::urlAdd($url, $type, $option['slug']),
								'option' => $option
							);
						}
					}
				}
			}
		}

		uksort($prep, function ($a, $b) {
			if ($a == $b) {
				return 0;
			}

			// prioritize these
			$priority = array('post_type', 'category', 'post_tag');

			$pa = array_search($a, $priority);
			$pb = array_search($b, $priority);

			if ($pa !== false && $pb !== false) {
				return ($pa < $pb) ? 0 : 1;
			}

			if ($pa !== false || $pb !== false) {
				return $pa === false;
			}

			// then by alpha
			return ($a < $b) ? 0 : 1;
		});

		if (count($prep) > 0) {
			echo '<form action="' . $url . '" method="GET" id="esajaxform">';

			foreach ($prep as $type => $settings) {
				$style = $settings['show'] ? '' : 'style="display:none"';

				echo '<aside id="facet-' . $type . '-available" class="widget facets facets-available" ' . $style . '>';

				echo '<h3 class="widget-title"><span class="widget-title-inner">' . ($settings['name'] == 'post_type' ? 'Content Type' : $settings['name']) . '</span></h3>';

				if ($async) {
					echo '<p class="facet-empty" style="display:none">You can not filter the results anymore.</p>';
				}

				echo '<ul>';

				foreach ($settings['avail'] as $avail) {
					$style = $avail['option']['count'] < $wp_query->found_posts ? '' : 'style="display:none"';

					echo '<li id="facet-' . $type . '-' . $avail['option']['slug'] . '" class="facet-item" ' . $style . '>';

					if ($async) {
						printf('<input type="checkbox" name="es[%s][and][]" value="%s" />%s <span class="count">(%d)</span>', $type, $avail['option']['slug'],
							$avail['option']['name'], $avail['option']['count']);
					} else {
						echo '<a href="' . $avail['url'] . '">' . $avail['option']['name'] . ' (' . $avail['option']['count'] . ')</a>';
					}

					echo '</li>';
				}

				echo '</ul>';

				echo '</aside>';
			}

			if ($async) {
				echo '<span class="clear"><a href="#" class="clear-inner esclear">Clear All</a></span>';
			}

			echo '</form>';
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
			'asyncReplace' => '',
			'cssOffset' => '',
		);

		$instance = array_merge($defaults, $instance);

		?>
		<p>
			<input class="checkbox" type="checkbox" <?php checked(isset($instance['async']) ? $instance['async'] : false, true); ?>
				   id="<?php echo $this->get_field_id('async'); ?>" name="<?php echo $this->get_field_name('async'); ?>" value="1"/>
			<label for="<?php echo $this->get_field_id('async'); ?>">Update page content asynchronously</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('asyncReplace'); ?>">CSS Selector of the container to copy/replace</label>
			<input id="<?php echo $this->get_field_id('asyncReplace'); ?>" name="<?php echo $this->get_field_name('asyncReplace'); ?>"
				   value="<?php echo isset($instance['asyncReplace']) ? htmlspecialchars($instance['asyncReplace']) : ''; ?>"
				   style="width:100%;"/>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('cssOffset'); ?>">CSS Selector of item to offset against when scrolling to top (ie:
				floating header)</label>
			<input id="<?php echo $this->get_field_id('cssOffset'); ?>" name="<?php echo $this->get_field_name('cssOffset'); ?>"
				   value="<?php echo isset($instance['cssOffset']) ? htmlspecialchars($instance['cssOffset']) : ''; ?>"
				   style="width:100%;"/>
		</p>
		<?php
	}
}

add_action('widgets_init', function () {
	register_widget('FacetingOptionsWidget');
});

?>
