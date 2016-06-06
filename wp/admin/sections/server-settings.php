<?php
namespace elasticsearch;

add_action('nhp-opts-options-validate-elasticsearch', function ($new, $current) {
	global $NHP_Options;

	if ($new['server_url'] == $current['server_url'] && $new['server_index'] == $current['server_index']) {
		return;
	}

	if ($new['server_url']) {
		$client = new \Elastica\Client(array(
			'url' => $new['server_url']
		));

		try {
			$index = $client->getIndex($new['server_index']);

			$status = $index->getStats()->getData();
		} catch (\Elastica\Exception\ResponseException $ex) {
			// This kind usually means there was an issue with the index not existing, so we'll associate the message to that field.
			$field = $NHP_Options->sections['server']['fields']['server_index'];
			$field['msg'] = __($ex->getMessage());

			$NHP_Options->errors[] = $field;

			set_transient('nhp-opts-errors-elasticsearch', $NHP_Options->errors, 1000);
			return;
		} catch (\Exception $ex) {
			$field = $NHP_Options->sections['server']['fields']['server_url'];
			$field['msg'] = __($ex->getMessage());

			$NHP_Options->errors[] = $field;

			set_transient('nhp-opts-errors-elasticsearch', $NHP_Options->errors, 1000);
			return;
		}

		try {
			$index = $client->getIndex($new['secondary_index']);

			$status = $index->getStats()->getData();
		} catch (\Elastica\Exception\ResponseException $ex) {
			// This kind usually means there was an issue with the index not existing, so we'll associate the message to that field.
			$field = $NHP_Options->sections['server']['fields']['secondary_index'];
			$field['msg'] = __($ex->getMessage());

			$NHP_Options->errors[] = $field;

			set_transient('nhp-opts-errors-elasticsearch', $NHP_Options->errors, 1000);
			return;
		} catch (\Exception $ex) {
			$field = $NHP_Options->sections['server']['fields']['server_url'];
			$field['msg'] = __($ex->getMessage());

			$NHP_Options->errors[] = $field;

			set_transient('nhp-opts-errors-elasticsearch', $NHP_Options->errors, 1000);
			return;
		}

		if (empty($status) || empty($status['indices']) || empty($status['indices'][$new['server_index']])) {
			$field = $NHP_Options->sections['server']['fields']['server_url'];
			$field['msg'] = 'Unable to connect to the ElasticSearch server.';

			$NHP_Options->errors[] = $field;

			set_transient('nhp-opts-errors-elasticsearch', $NHP_Options->errors, 1000);
		}
	}
}, 10, 2);

$sections['server'] = array(
	'icon' => NHP_OPTIONS_URL . 'img/glyphicons/glyphicons_280_settings.png',
	'title' => 'Server Settings',
	'fields' => array(
		'server_url' => array(
			'id' => 'server_url',
			'type' => 'text',
			'title' => 'Server URL',
			'sub_desc' => 'If your search provider has given you a connection URL, use that instead of filling out server information.',
			'desc' => 'It must include the trailing slash "/"'
		),
		'server_index' => array(
			'id' => 'server_index',
			'type' => 'text',
			'title' => 'Index Name'
		),
		'secondary_index' => array(
			'id' => 'secondary_index',
			'type' => 'text',
			'sub_desc' => 'If defined, a wipe of data will actually run against the secondary index first, allowing you to re-populate (without touching production) and then swap after.',
			'title' => 'Secondary Index Name'
		),
		'server_timeout_read' => array(
			'id' => 'server_timeout_read',
			'type' => 'text',
			'title' => 'Read Timeout',
			'validate' => 'numeric',
			'std' => 1,
			'desc' => 'Number of seconds (minimum of 1)',
			'sub_desc' => 'The maximum time (in seconds) that <strong>read</strong> requests should wait for server response. If the call times out, wordpress will fallback to standard search.'
		),
		'server_timeout_write' => array(
			'id' => 'server_timeout_write',
			'type' => 'text',
			'title' => 'Write Timeout',
			'validate' => 'numeric',
			'std' => 300,
			'desc' => 'Number of seconds (minimum of 1)',
			'sub_desc' => 'The maximum time (in seconds) that <strong>write</strong> requests should wait for server response. This should be set long enough to index your entire site.'
		)
	)
);

?>
