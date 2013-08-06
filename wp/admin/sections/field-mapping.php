<?php
namespace elasticsearch;

$fields = array(
	'numeric' => array(
		'id' => 'numeric',
		'type' => 'multi_checkbox',
		'title' => 'Numeric Fields',
		'desc' => 'Any field marked as "numeric" will enabled support for range faceting.'
	),
	'not_analyzed' => array(
		'id' => 'not_analyzed',
		'type' => 'multi_checkbox',
		'title' => 'Non Analyzed Fields',
		'desc' => 'Any string field marked as "non analyzed" will require search terms to match the entire value instead of any words in the value.'
	)
);

foreach(Config::fields() as $field){
	if($field != 'post_date'){
		$fields['numeric']['options'][$field] = $field;
		$fields['not_analyzed']['options'][$field] = $field;
	}
}

foreach(Config::meta_fields() as $field){
		$fields['numeric']['options'][$field] = $field;
		$fields['not_analyzed']['options'][$field] = $field;
}

$numeric_option = Config::option('numeric');

if ($numeric_option) {
	foreach(array_keys($numeric_option) as $numeric){
		$fields[$numeric . '_range'] = array(
			'id' => $numeric . '_range',
			'type' => 'text',
			'title' => $numeric . ' Range',
			'desc' => 'Comma delimited list of ranges for this field using the format of FROM-TO. Currently ranges are always inclusive., ie: "-10,10-50,50-" or "-5,6-,7-,8-,9-"'
		);
	}
}

$sections['field'] = array(
	'icon' => NHP_OPTIONS_URL.'img/glyphicons/glyphicons_097_vector_path_line.png',
	'title' => 'Field Mapping',
	'desc' => 'Finer grain control over how data is interpreted inside of ElasticSearch. Any changes made in this tab will require you to clear then re-index your data.',
	'fields' => $fields
);

add_action('nhp-opts-options-validate-elasticsearch', function(){
	global $NHP_Options;

	if($_POST['elasticsearch']['last_tab'] == 'field'){
		try{
			foreach(Config::fields() as $field){
				if($_POST['elasticsearch']['numeric'][$field]){
					$index = Indexer::_index(false);

					foreach(Config::types() as $type){
						$type = $index->getType($type);

						$mapping = new \Elastica\Type\Mapping($type);
						$mapping->setProperties(array($field => array(
							'type' => 'float',
							'store' => 'yes',

						)));

						$mapping->send();
					}
				}
			}
		}catch(\Exception $ex){
			error_log($ex);
			
			$field = $NHP_Options->sections['field']['fields']['numeric'];
			$field['msg'] = 'There was a problem configuring field mapping.';

			$NHP_Options->errors[] = $field;

			set_transient('nhp-opts-errors-elasticsearch', $NHP_Options->errors, 1000 );
		}
	}
});

?>
