<?php 
namespace elasticsearch;

echo $before_title . $title . $after_title;   
?>
	<div class="charts-widget">
		<ul>
<?php
	foreach ($selected_filters as $filter) {
		switch($filter['mode']) {
	        case 'pie':			$this->print_pie($filter['name']);		 break;
	        case 'doughnut':	$this->print_doughnut($filter['name']);	 break;
	        case 'radar':		$this->print_radar($filter['name']);	 break;
	        case 'polararea':	$this->print_polararea($filter['name']); break;
	        case 'bar':			$this->print_bar($filter['name']);		 break;
	        case 'line':		$this->print_line($filter['name']);		 break;
	    	} //end switch
	} //end foreach
	
	echo '</li></ul></div>';
