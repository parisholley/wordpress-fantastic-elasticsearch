<?php
namespace elasticsearch;

$sections['facet'] = array(
	'icon' => NHP_OPTIONS_URL.'img/glyphicons/glyphicons_150_check.png',
	'title' => 'Facet Widget',
	'desc' => 'Select options for sidebar facet widget.',
	'fields' => array(
		'auto_submit' => array(
			'id' => 'auto_submit',
			'type' => 'checkbox',
			'title' => 'Auto submit form?',
			'options' => array(
				'auto_submit' => ''),
			'std' => 'auto_submit'//this should be the key as defined above
		),
		'hide_empty' => array(
			'id' => 'hide_empty',
			'type' => 'checkbox',
			'title' => 'Hide empty terms?',
			'options' => array(
				'hide_empty' => ''),
			'std' => 'hide_empty'//this should be the key as defined above
		),
		'display_search_box' => array(
			'id' => 'display_search_box',
			'type' => 'checkbox',
			'title' => 'Display the search box?',
			'options' => array(
				'display_search_box' => '') 
		),
		'display_reset_button' => array(
			'id' => 'display_reset_button',
			'type' => 'checkbox',
			'title' => 'Display the reset button?',
			'options' => array(
				'display_reset_button' => '')
		),
		'post_count' => array(
			'id' => 'post_count',
			'type' => 'radio',
			'title' => 'Display post count:',
			'options' => array(
				'total' => 'Total', 
				'dynamic' => 'Dynamic', 
				'none' => 'None'), 
			'std' => 'dynamic'//this should be the key as defined above
		),
		'multiple_relation' => array(
			'id' => 'multiple_relation',
			'type' => 'select',
			'title' => 'Multiple selection relation:', 
			'options' => array(
				'[or]' => 'OR', 
				'[and]' => 'AND'),
			'std' => '[or]'//this should be the key as defined above
		)
	)
);
?>