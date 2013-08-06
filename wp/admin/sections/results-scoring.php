<?php
namespace elasticsearch;

$fields = array();

foreach(Config::fields() as $field){
	$fields[] = array(
		'id' => 'score_field_' . $field,
		'type' => 'text',
		'validation' => 'numeric',
		'desc' => 'A numeric value (if 0, it will have no influence)',
		'title' => "Field: $field",
		'std' => 1
	);
}

foreach(Config::taxonomies() as $tax){
	$fields[] = array(
		'id' => 'score_tax_' . $tax,
		'type' => 'text',
		'validation' => 'numeric',
		'desc' => 'A numeric value (if 0, it will have no influence)',
		'title' => "Taxonomy: $tax",
		'std' => 2
	);
}

foreach(Config::meta_fields() as $field){
	$fields[] = array(
		'id' => 'score_meta_' . $field,
		'type' => 'text',
		'validation' => 'numeric',
		'desc' => 'A numeric value (if 0, it will have no influence)',
		'title' => "Meta: $field",
		'std' => 3
	);
}

$sections['scoring'] = array(
	'icon' => NHP_OPTIONS_URL.'img/glyphicons/glyphicons_079_signal.png',
	'title' => 'Result Scoring',
	'desc' => 'When executing a search, not all content is created equal. Review each of the items that are indexed and order them by the most relevant/important to least relevant/important.',
	'fields' => $fields
);

?>