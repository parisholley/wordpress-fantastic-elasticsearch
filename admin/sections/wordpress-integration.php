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
			'sub_desc' => 'If enabled, category listings will use ElasticSearch for filtering and faceting. To use faceting, read the <a href="https://github.com/parisholley/wordpress-fantastic-elasticsearch/wiki/Faceted-Search">facted search implementation instructions</a>'
		),
		'enable_taxonomies' => array(
			'id' => 'enable_taxonomies',
			'type' => 'taxes_multi_select',
			'title' => 'Taxonomies to Enable',
			'sub_desc' => 'If enabled, taxonomy listings will use ElasticSearch for filtering and faceting. To use faceting, read the <a href="https://github.com/parisholley/wordpress-fantastic-elasticsearch/wiki/Faceted-Search">facted search implementation instructions</a>'
		),
		'enable_pages' => array(
			'id' => 'enable_pages',
			'type' => 'pages_multi_select',
			'title' => 'Pages to Enable',
			'sub_desc' => 'If enabled, page listings will use ElasticSearch for filtering and faceting. To use faceting, read the <a href="https://github.com/parisholley/wordpress-fantastic-elasticsearch/wiki/Faceted-Search">facted search implementation instructions</a>'
		),
		'enable_post_typs' => array(
			'id' => 'enable_post_types',
			'type' => 'post_types_multi_select',
			'title' => 'Post Types to Enable',
			'sub_desc' => 'If enabled, post type listings will use ElasticSearch for filtering and faceting. To use faceting, read the <a href="https://github.com/parisholley/wordpress-fantastic-elasticsearch/wiki/Faceted-Search">facted search implementation instructions</a>'
		)
	)
);

?>