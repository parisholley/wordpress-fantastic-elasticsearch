<?php
namespace elasticsearch;

$sections['integration'] = array(
	'icon' => NHP_OPTIONS_URL.'img/glyphicons/glyphicons_083_random.png',
	'title' => 'Wordpress Integration',
	'fields' => array(
		'enable' => array(
			'id' => 'enable',
			'type' => 'checkbox',
			'title' => 'Enable Search',
			'sub_desc' => 'If enabled, the default wordpress search will use ElasticSearch.'
		),
		'enable_categories' => array(
			'id' => 'enable_categories',
			'type' => 'cats_multi_select',
			'title' => 'Categories to Enable',
			'sub_desc' => 'If enabled, category listings will use ElasticSearch for filtering and faceting. To use faceting, read the <a href="https://github.com/parisholley/wordpress-fantastic-elasticsearch/wiki/Faceted-Search">facted search implementation instructions</a>',
			'args' => array()
		)
	)
);

?>
