<?php

// Add IE Fallback for HTML5 and canvas
// - - - - - - - - - - - - - - - - - - - - - - -
function wp_charts_html5_support () {
    echo '<!--[if lt IE 8]>';
    echo '<script src="'.plugins_url( '/js/excanvas.compiled.js', __FILE__ ).'"</script>';
    echo '<![endif]-->';
    echo '	<style>
    			/*wp_charts_js responsive canvas CSS override*/
    			.wp_charts_canvas {
    				width:100%!important;
    				max-width:100%;
    				height:auto!important;
    			}
    		</style>';
}

// Register Script
// - - - - - - - - - - - - - - - - - - - - - - -
function wp_charts_load_scripts() {

	if ( !is_Admin() ) {
		wp_register_script( 'charts-js', plugins_url('/js/chart.js', __FILE__) );
		wp_enqueue_script( 'charts-js' );
	}

}

add_action( "wp_enqueue_scripts", "wp_charts_load_scripts" );
add_action('wp_head', 'wp_charts_html5_support');

// make sure there are the right number of colours in the colour array
// - - - - - - - - - - - - - - - - - - - - - - -
if ( !function_exists('wp_charts_compare_fill') ) {
	function wp_charts_compare_fill(&$measure,&$fill) {
		// only if the two arrays don't hold the same number of elements
		if (count($measure) != count($fill)) {
		    // handle if $fill is less than $measure
		    while (count($fill) < count($measure) ) {
		        $fill = array_merge( $fill, array_values($fill) );
		    }
		    // handle if $fill has more than $measure
		    $fill = array_slice($fill, 0, count($measure));
		}
	}
}

// color conversion function
// - - - - - - - - - - - - - - - - - - - - - - -
if (!function_exists( "wp_charts_hex2rgb" )) {
	function wp_charts_hex2rgb($hex) {
	   $hex = str_replace("#", "", $hex);

	   if(strlen($hex) == 3) {
	      $r = hexdec(substr($hex,0,1).substr($hex,0,1));
	      $g = hexdec(substr($hex,1,1).substr($hex,1,1));
	      $b = hexdec(substr($hex,2,1).substr($hex,2,1));
	   } else {
	      $r = hexdec(substr($hex,0,2));
	      $g = hexdec(substr($hex,2,2));
	      $b = hexdec(substr($hex,4,2));
	   }

	   $rgb = array($r, $g, $b);
	   return implode(",", $rgb); // returns the rgb values separated by commas
	}
}

if (!function_exists('wp_charts_trailing_comma')) {
	function wp_charts_trailing_comma($incrementor, $count, &$subject) {
		$stopper = $count - 1;
		if ($incrementor !== $stopper) {
			return $subject .= ',';
		}
	}
}

// Chart Shortcode 1
// - - - - - - - - - - - - - - - - - - - - - - -
function wp_charts( $atts ) {

	// Default Attributes
	// - - - - - - - - - - - - - - - - - - - - - - -
	extract( shortcode_atts(
		array(
			'type'             => 'pie',
			'title'            => 'chart',
			'canvaswidth'      => '625',
			'canvasheight'     => '625',
			'width'			   => '48%',
			'height'		   => 'auto',
			'margin'		   => '5px',
			'align'            => '',
			'class'			   => '',
			'labels'           => '',
			'data'             => '30,50,100',
			'datasets'         => '30,50,100 next 20,90,75',
			'colors'           => '69D2E7,#E0E4CC,#F38630,#96CE7F,#CEBC17,#CE4264',
			'fillopacity'      => '0.7',
			'pointstrokecolor' => '#FFFFFF',
			'scaleSteps'	   => '5',
			'scaleStepWidth'   => '1',
			'scaleStartValue'  => '20',
			'animation'  => 'true',
			'show_charts_count' => 'true'

		), $atts )
	);

	// prepare data
	// - - - - - - - - - - - - - - - - - - - - - - -
	$title    = str_replace(' ', '', $title);
	$data     = explode(',', str_replace(' ', '', $data));
	$datasetsCount = explode(",", $datasets);
	$datasets = explode("next", str_replace(' ', '', $datasets));
	$colors   = explode(',', str_replace(' ','',$colors));
	(strpos($type, 'lar') !== false ) ? $type = 'PolarArea' : $type = ucwords($type) ;

	// output - covers Pie, Doughnut, and PolarArea
	// - - - - - - - - - - - - - - - - - - - - - - -
	$currentchart = '<div class="'.$align.' '.$class.'" style="width:'.$width.';height:'.$height.';margin:'.$margin.';">';
	$currentchart .= '<canvas id="'.$title.'" height="'.$canvasheight.'" width="'.$canvaswidth.'" class="wp_charts_canvas"></canvas></div>
	<script>';

	// start the js arrays correctly depending on type
	if ($type == 'Line' || $type == 'Radar' || $type == 'Bar' ) {

		wp_charts_compare_fill($datasets, $colors);
		$total    = count($datasets);

		// output labels
		$currentchart .= 'var '.$title.'Data = {';
		// if ( count($labels) > 0 ) {

			$currentchart .= 'labels : [';
			$labelstrings = explode(',',$labels);
			// wp_charts_compare_fill($datasets, $labelstrings);
			for ($j = 0; $j < count($labelstrings); $j++ ) {
				if ($show_charts_count == "true"){
					$currentchart .= '"'.$labelstrings[$j].' ('.$datasetsCount[$j].')"';
				}else{
					$currentchart .= '"'.$labelstrings[$j].'"';
				}
				wp_charts_trailing_comma($j, count($labelstrings), $currentchart);
			}
			$currentchart .= 	'],';
		// }

		$currentchart .= 'datasets : [';
	} else {

		wp_charts_compare_fill($data, $colors);
		$total = count($data);

		$currentchart .= 'var '.$title.'Data = [';
	}

		// create the javascript array of data and attr correctly depending on type
		for ($i = 0; $i < $total; $i++) {

			if ($type === 'Pie' || $type === 'Doughnut' || $type === 'PolarArea') {
				$labelstrings = explode(',',$labels);
				$currentchart .= '{
					value 	: '. $data[$i] .',
					color 	: '.'"'. $colors[$i].'"'.',';
					if ($show_charts_count == "true"){
						$currentchart .= 'label   : '.'"'. $labelstrings[$i].' ('.$data[$i].')"';
					}else{
						$currentchart .= 'label   : '.'"'. $labelstrings[$i].'"';
					}
				$currentchart .= '}';

			} else if ($type === 'Bar') {

				$currentchart .= '{
					fillColor 	: "rgba('. wp_charts_hex2rgb( $colors[$i] ) .','.$fillopacity.')",
					strokeColor : "rgba('. wp_charts_hex2rgb( $colors[$i] ) .',1)",
					data 		: ['.$datasets[$i].']
				}';

			} else if ($type === 'Line' || $type === 'Radar') {

				$currentchart .= '{
					fillColor 	: "rgba('. wp_charts_hex2rgb( $colors[$i] ) .','.$fillopacity.')",
					strokeColor : "rgba('. wp_charts_hex2rgb( $colors[$i] ) .',1)",
					pointColor 	: "rgba('. wp_charts_hex2rgb( $colors[$i] ) .',1)",
					pointStrokeColor : "'.$pointstrokecolor.'",
					data 		: ['.$datasets[$i].']
				}';

			}  // end type conditional

			wp_charts_trailing_comma($i, $total, $currentchart);

		}

		// end the js arrays correctly depending on type
		if ($type == 'Line' || $type == 'Radar' || $type == 'Bar') {
			$currentchart .=	']
						};';
		} else {
			$currentchart .=	'];';
		}

		// create the javascript array of data and attr correctly depending on type

		if ($type == 'Line' || $type == 'Radar' || $type == 'Bar' || $type === 'PolarArea') {

			$currentchart .= 'var '.$title.'Options = {
				scaleOverride : true,
				scaleSteps : '.$scaleSteps.',
				scaleStepWidth : '.$scaleStepWidth.',
				scaleStartValue : '.$scaleStartValue.',
				animation : '.$animation.'
			};';
		}else{
			$currentchart .= 'var '.$title.'Options = {
				animation : '.$animation.'
			};';
		}

		$currentchart .= 'var wpChart'.$title.$type.' = new Chart(document.getElementById("'.$title.'").getContext("2d")).'.$type.'('.$title.'Data,'.$title.'Options);
	</script>';
	
	// return the final result
	// - - - - - - - - - - - - - - - - - - - - - - -
	return $currentchart;
}