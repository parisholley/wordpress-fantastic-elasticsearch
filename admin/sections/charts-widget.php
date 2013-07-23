<?php
namespace elasticsearch;

$sections['charts'] = array(
	'icon' => NHP_OPTIONS_URL.'img/glyphicons/glyphicons_042_pie_chart.png',
	'title' => 'Charts Widget',
	'desc' => 'Select options for sidebar charts widget.',
	'fields' => array(
		'charts_widget_info' => array(
			'id' => 'charts_widget_info',
			'type' => 'info',
			'desc' => 'The Charts Widget takes the facet counts tabulated by Elasticsearch and creates one of the following six charts using charts.js.',
			'title' => 'Charts Widget Info',
		),
		'line_charts' => array(
			'id' => 'line_charts',
			'type' => 'info',
			'desc' => '<div class="info_field_text">Line graphs are probably the most widely used graph for showing trends. Chart.js has a ton of customisation features for line graphs, along with support for multiple datasets to be plotted on one chart.</div>
						<p></p>
					   <div class="chart_pic" style="width:50%" height:"auto" margin:"auto" >
						<canvas id="lineChartCanvas" width="449" height="300" style="width: 449px height: 300px"></canvas>
						<script>
						var lineDemoData = {
							labels : ["January","February","March","April","May","June","July"],
							datasets : [
								{
									fillColor : "rgba(220,220,220,0.5)",
									strokeColor : "rgba(220,220,220,1)",
									pointColor : "rgba(220,220,220,1)",
									pointStrokeColor : "#fff",
									data : [65,59,90,81,56,55,40]
								},
								{
									fillColor : "rgba(151,187,205,0.5)",
									strokeColor : "rgba(151,187,205,1)",
									pointColor : "rgba(151,187,205,1)",
									pointStrokeColor : "#fff",
									data : [28,48,40,19,96,27,100]
								}
							]
						};
						var lineDemo = new Chart(document.getElementById("lineChartCanvas").getContext("2d")).Line(lineDemoData);
						</script>',
			'title' => 'Line Charts',
		),
		'bar_charts' => array(
			'id' => 'bar_charts',
			'type' => 'info',
			'desc' => '<div class="info_field_text">Bar graphs are also great at showing trend data. Chart.js supports bar charts with a load of custom styles and the ability to show multiple bars for each x value.</div>
						<p></p>
					   <div class="chart_pic" style="width:50%" height:"auto" margin:"auto" >
						<canvas id="barChartCanvas" width="449" height="300" style="width: 449px height: 300px"></canvas>
						<script>
						var barDemoData = {
							labels : ["January","February","March","April","May","June","July"],
							datasets : [
								{
									fillColor : "rgba(220,220,220,0.5)",
									strokeColor : "rgba(220,220,220,1)",
									data : [65,59,90,81,56,55,40]
								},
								{
									fillColor : "rgba(151,187,205,0.5)",
									strokeColor : "rgba(151,187,205,1)",
									data : [28,48,40,19,96,27,100]
								}
							]
						};
						var barDemo = new Chart(document.getElementById("barChartCanvas").getContext("2d")).Bar(barDemoData);
						</script>',
			'title' => 'Bar Charts',
		),
		'radar_charts' => array(
			'id' => 'radar_charts',
			'type' => 'info',
			'desc' =>  '<div class="info_field_text">Radar charts are good for comparing a selection of different pieces of data. Chart.js supports multiple data sets plotted on the same radar chart. It also supports all of the customisation and animation options you would expect.</div>
					   <p></p>
					   <div class="chart_pic" style="width:50%" height:"auto" margin:"auto" >
						<canvas id="radarChartCanvas" width="449" height="300" style="width: 449px height: 300px"></canvas>
						<script>
						var radarDemoDatasets = {
						labels : ["Eating","Drinking","Sleeping","Designing","Coding","Partying","Running"],
						datasets : [
							{
								fillColor : "rgba(220,220,220,0.5)",
								strokeColor : "rgba(220,220,220,1)",
								pointColor : "rgba(220,220,220,1)",
								pointStrokeColor : "#fff",
								data : [65,59,90,81,56,55,40]
							},
							{
								fillColor : "rgba(151,187,205,0.5)",
								strokeColor : "rgba(151,187,205,1)",
								pointColor : "rgba(151,187,205,1)",
								pointStrokeColor : "#fff",
								data : [28,48,40,19,96,27,100]
							}
						]};
						var radarDemo = new Chart(document.getElementById("radarChartCanvas").getContext("2d")).Radar(radarDemoDatasets);
						</script>',
			'title' => 'Radar Charts',
		),
		'pie_charts' => array(
			'id' => 'pie_charts',
			'type' => 'info',
			'desc' => '<div class="info_field_text">Pie charts are great at comparing proportions within a single data set. Chart.js shows animated pie charts with customisable colours, strokes, animation easing and effects.</div>
					   <p></p>
					   <div class="chart_pic" style="width:50%" height:"auto" margin:"auto" >
						<canvas id="pieChartCanvas" width="449" height="300" style="width: 449px height: 300px"></canvas>
						<script>
						var pieDemoData = [
							{
								value: 30,
								color:"#F38630",
								label: "Apple"
							},
							{
								value : 50,
								color : "#E0E4CC",
								label: "Pecan"
							},
							{
								value : 100,
								color : "#69D2E7",
								label: "Pumpkin"
							}			
						];
						var pieDemo = new Chart(document.getElementById("pieChartCanvas").getContext("2d")).Pie(pieDemoData);
						</script>',
			'title' => 'Pie Charts',
		),
		'polar_area_charts' => array(
			'id' => 'polar_area_charts',
			'type' => 'info',
			'desc' => '<div class="info_field_text">Polar area charts are similar to pie charts, but the variable is not the circumference of the segment, but the radius of it. Chart.js delivers animated polar area charts with custom coloured segments, along with customisable scales and animation.</div>
						<p></p>
					   <div class="chart_pic" style="width:50%" height:"auto" margin:"auto" >
						<canvas id="polarAreaChartCanvas" width="449" height="300" style="width: 449px height: 300px"></canvas>
						<script>
						var polarAreaDemoData = [
							{
								value : 85,
								color: "#D97041",
								label: "North Pole"
							},
							{
								value : 90,
								color: "#C7604C",
								label: "Antarctica"
							},
							{
								value : 80,
								color: "#9D9B7F",
								label: "Siberia"
							}
						];
						var polarAreaDemoOptions = {
							scaleOverride : true,
							scaleSteps : 4,
							scaleStepWidth : 5,
							scaleStartValue : 70
						};
						var polarAreaDemo = new Chart(document.getElementById("polarAreaChartCanvas").getContext("2d")).PolarArea(polarAreaDemoData,polarAreaDemoOptions);
						</script>',
			'title' => 'Polar Area Charts',
		),
		'doughnut_charts' => array(
			'id' => 'doughnut_charts',
			'type' => 'info',
			'desc' => '<div class="info_field_text">Similar to pie charts, doughnut charts are great for showing proportional data. Chart.js offers the same customisation options as for pie charts, but with a custom sized inner cutout to turn your pies into doughnuts.</div>
						<p></p>
					   <div class="chart_pic" style="width:50%" height:"auto" margin:"auto" >
						<canvas id="doughnutChartCanvas" width="449" height="300" style="width: 449px height: 300px"></canvas>
						<script>
						var doughnutDemoData = [
							{
								value: 30,
								color:"#F7464A",
								label: "Jelly"
							},
							{
								value : 50,
								color : "#E2EAE9",
								label: "Glazed"
							},
							{
								value : 100,
								color : "#D4CCC5",
								label: "Cake"
							},
							{
								value : 40,
								color : "#949FB1",
								label: "Long John"
							},
							{
								value : 120,
								color : "#BCED91",
								label: "Custard"
							}

						];
						var doughnutDemo = new Chart(document.getElementById("doughnutChartCanvas").getContext("2d")).Doughnut(doughnutDemoData);
						</script>',
			'title' => 'Doughnut Charts',
		),
		'charts_js_info' => array(
			'id' => 'charts_js_info',
			'type' => 'info',
			'desc' => 'The Charts Widget is based on the charts.js library and the <a href= "http://wordpress.org/plugins/wp-charts/" target="_blank">WordPress Charts</a> plugin. For more information on all of the options provided by charts.js, please visit <a href= "http://www.chartjs.org/" target="_blank">chartsjs.org</a>.',
			'title' => 'Charts.js Info',
		)
	)
);
?>
