<?php 
namespace elasticsearch;

echo $before_title . $title . $after_title;   
?>
	<div class="charts-widget">
		<ul>
<?php
	foreach ($selected_filters as $filter) {
		switch($filter['mode']) {
	        case 'pie':			echo wp_charts(array(
												'title' => Charts_Widget::tax_name($filter['name']),
												'type' => "pie", 
												'data' => Charts_Widget::count_data($filter['name']),
												'width' => $width,
												'colors' => $colors,
												'labels' => Charts_Widget::labels($filter['name']),
												'animation' => $animation,
												'show_charts_count' => $show_charts_count
												));		 break;
	        case 'doughnut':	echo wp_charts(array(
												'title' => Charts_Widget::tax_name($filter['name']),
												'type' => "doughnut", 
												'data' => Charts_Widget::count_data($filter['name']),
												'width' => $width,
												'colors' => $colors,
												'labels' => Charts_Widget::labels($filter['name']),
												'animation' => $animation,
												'show_charts_count' => $show_charts_count
												));		 break;
	        case 'radar':		echo wp_charts(array(
												'title' => Charts_Widget::tax_name($filter['name']),
												'type' => "radar", 
												'datasets' => Charts_Widget::count_datasets($filter['name']),
												'width' => $width,
												'colors' => $colors,
												'labels' => Charts_Widget::labels($filter['name']),
												'scaleSteps' => $scaleSteps,
												'scaleStepWidth' => $scaleStepWidth, 
												'scaleStartValue' => $scaleStartValue,
												'animation' => $animation,
												'show_charts_count' => $show_charts_count
												));		 break;
	        case 'polararea':	echo wp_charts(array(
												'title' => Charts_Widget::tax_name($filter['name']),
												'type' => "polararea", 
												'data' => Charts_Widget::count_data($filter['name']),
												'width' => $width,
												'colors' => $colors,
												'fillopacity' => $fillopacity,
												'labels' => Charts_Widget::labels($filter['name']),
												'scaleSteps' => $scaleSteps,
												'scaleStepWidth' => $scaleStepWidth, 
												'scaleStartValue' => $scaleStartValue,
												'animation' => $animation,
												'show_charts_count' => $show_charts_count
												));		 break;
	        case 'bar':			echo wp_charts(array(
												'title' => Charts_Widget::tax_name($filter['name']),
												'type' => "bar", 
												'datasets' => Charts_Widget::count_datasets($filter['name']),
												'width' => $width,
												'colors' => $colors, 
												'fillopacity' => $fillopacity,
												'labels' => Charts_Widget::labels($filter['name']),
												'scaleSteps' => $scaleSteps,
												'scaleStepWidth' => $scaleStepWidth, 
												'scaleStartValue' => $scaleStartValue,
												'animation' => $animation,
												'show_charts_count' => $show_charts_count
												));		 break;
	        case 'line':		echo wp_charts(array(
												'title' => Charts_Widget::tax_name($filter['name']),
												'type' => "line", 
												'datasets' => Charts_Widget::count_datasets($filter['name']),
												'width' => $width,
												'colors' => $colors,
												'labels' => Charts_Widget::labels($filter['name']),
												'scaleSteps' => $scaleSteps,
												'scaleStepWidth' => $scaleStepWidth, 
												'scaleStartValue' => $scaleStartValue,
												'animation' => $animation,
												'show_charts_count' => $show_charts_count
												));		 break;
	    	} //end switch
	} //end foreach
	
	echo '</li></ul></div>';


