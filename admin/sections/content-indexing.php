<?php
namespace elasticsearch;

$fields = array(
	array(
		'id' => 'fields',
		'type' => 'multi_checkbox',
		'title' => 'Index Fields'
	),
	array(
		'id' => 'types',
		'type' => 'multi_checkbox',
		'title' => 'Post Types'
	),
	array(
		'id' => 'taxonomies',
		'type' => 'multi_checkbox',
		'title' => 'Taxonomy Fields'
	)
);

foreach(Defaults::fields() as $field){
	$fields[0]['options'][$field] = $field;
	$fields[0]['std'][$field] = 1;
}


foreach(Defaults::types() as $type){
	$fields[1]['options'][$type] = $type;
	$fields[1]['std'][$type] = 1;
}

foreach(Defaults::taxonomies(Defaults::types()) as $tax){
	$fields[2]['options'][$tax] = $tax;
	$fields[2]['std'][$tax] = 1;
}

$sections['content'] = array(
	'icon' => NHP_OPTIONS_URL.'img/glyphicons/glyphicons_036_file.png',
	'title' => 'Content Indexing',
	'desc' => 'Select which information you would like added to the search index.',
	'fields' => $fields
);
?>