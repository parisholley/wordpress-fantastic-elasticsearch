<?php
namespace elasticsearch;

$sections['map'] = array(
	'icon' => NHP_OPTIONS_URL.'img/glyphicons/glyphicons_242_google_maps.png',
	'title' => 'Map Widget',
	'fields' => array(
		'map_api_key' => array(
			'id' => 'map_api_key',
			'type' => 'text',
			'title' => 'Google Maps API Key',
			'sub_desc' => 'Getting a Google Maps API key is simple (though you will need a google account), and instructions can be found <a href= "https://developers.google.com/maps/documentation/javascript/tutorial" target="_blank"> here</a>.',
			'desc' => ''
		),
		'map_post_types' => array(
			'id' => 'map_post_types',
			'type' => 'post_types_multi_select',
			'title' => 'Show Address Box on Following Post Types',
			'sub_desc' => 'If enabled, post author can enter an address for each post that will be used by the Elastisearch Map Widget.'
		)
	)
);

?>