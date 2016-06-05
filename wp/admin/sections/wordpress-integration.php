<?php
namespace elasticsearch;

$sections['integration'] = array(
	'icon' => NHP_OPTIONS_URL . 'img/glyphicons/glyphicons_083_random.png',
	'title' => 'Wordpress Integration',
	'fields' => array(
		'enable' => array(
			'id' => 'enable',
			'type' => 'checkbox',
			'title' => 'Enable Search',
			'sub_desc' => 'If enabled, the default wordpress search will use ElasticSearch.'
		),
		'enable_all_categories' => array(
			'id' => 'enable_all_categories',
			'type' => 'checkbox',
			'title' => 'Enable for ALL category archives',
			'sub_desc' => 'If enabled, the faceting API/widgets will be available for category pages. Note: This will list content indexed in ElasticSearch and should not differ from default Wordpress behavior.'
		),
		'enable_categories' => array(
			'id' => 'enable_categories',
			'type' => 'cats_multi_select',
			'title' => 'Enable for specific category archives',
			'sub_desc' => 'Same behavior as above, but you can enable it on specific categories. Any left unselected will fallback to Wordpress and will not have access to faceting.',
			'args' => array()
		),
		'enable_all_tags' => array(
			'id' => 'enable_all_tags',
			'type' => 'checkbox',
			'title' => 'Enable for ALL tags archives',
			'sub_desc' => 'If enabled, the faceting API/widgets will be available for tag archives. Note: This will list content indexed in ElasticSearch and should not differ from default Wordpress behavior.'
		),
		'enable_all_posts' => array(
			'id' => 'enable_all_posts',
			'type' => 'checkbox',
			'title' => 'Enable for ALL custom post types archives',
			'sub_desc' => 'If enabled, the faceting API/widgets will be available for custom post type archives. Note: This will list content indexed in ElasticSearch and should not differ from default Wordpress behavior.'
		),
	)
);

?>
