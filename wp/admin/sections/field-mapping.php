<?php
namespace elasticsearch;

$fields = array(
	'numeric' => array(
		'id' => 'numeric',
		'type' => 'multi_checkbox',
		'title' => 'Numeric Fields',
		'desc' => 'Any field marked as "numeric" will enabled support for range faceting.',
		'options' => array()
	),
	'not_analyzed' => array(
		'id' => 'not_analyzed',
		'type' => 'multi_checkbox',
		'title' => 'Non Analyzed Fields',
		'options' => array(),
		'desc' => 'Any string field marked as "non analyzed" will require search terms to match the entire value instead of any words in the value.'
	)
);

foreach (array_merge(Config::fields(), Config::meta_fields()) as $field) {
	if ($field != 'post_date' && $field != 'post_type') {
		$fields['numeric']['options'][$field] = $field;
		$fields['not_analyzed']['options'][$field] = $field;
	}
}

$numeric_option = Config::option('numeric');

if ($numeric_option) {
	foreach (array_keys($numeric_option) as $numeric) {
		$fields[$numeric . '_range'] = array(
			'id' => $numeric . '_range',
			'type' => 'text',
			'title' => $numeric . ' Range',
			'desc' => 'Comma delimited list of ranges for this field using the format of FROM-TO. Currently ranges are always inclusive., ie: "-10,10-50,50-" or "-5,6-,7-,8-,9-"'
		);
	}
}

$sections['field'] = array(
	'icon' => NHP_OPTIONS_URL . 'img/glyphicons/glyphicons_097_vector_path_line.png',
	'title' => 'Field Mapping',
	'desc' => 'Finer grain control over how data is interpreted inside of ElasticSearch. Any changes made in this tab will require you to clear then re-index your data.',
	'fields' => $fields
);

?>
