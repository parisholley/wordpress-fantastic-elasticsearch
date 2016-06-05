<?php
namespace elasticsearch;

$fields = array(
	array(
		'id' => 'fuzzy',
		'type' => 'text',
		'desc' => 'The number of characters that can be swapped out to match a word. For example; 1 = anoth(a)r~ = anoth(e)r; 2 = (e)noth(a)r~ = (a)noth(e)r; 2 = an()th(u)r~ = an(o)th(e)r. The smaller the number, the better the performance. Leave this blank to disable fuzzy searching. ONLY WORKS FOR VERSIONS > 0.90.1.',
		'title' => 'Fuzziness Amount'
	)
);

foreach (Config::fields() as $field) {
	$fields[] = array(
		'id' => 'score_field_' . $field,
		'type' => 'text',
		'validation' => 'numeric',
		'desc' => 'A numeric value (if 0, it will have no influence)',
		'title' => "Field: $field",
		'std' => 1
	);
}

foreach (Config::taxonomies() as $tax) {
	$fields[] = array(
		'id' => 'score_tax_' . $tax,
		'type' => 'text',
		'validation' => 'numeric',
		'desc' => 'A numeric value (if 0, it will have no influence)',
		'title' => "Taxonomy: $tax",
		'std' => 2
	);
}

foreach (Config::meta_fields() as $field) {
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
	'icon' => NHP_OPTIONS_URL . 'img/glyphicons/glyphicons_079_signal.png',
	'title' => 'Result Scoring',
	'desc' => 'When executing a search, not all content is created equal. Review each of the items that are indexed and order them by the most relevant/important to least relevant/important.',
	'fields' => $fields
);

?>