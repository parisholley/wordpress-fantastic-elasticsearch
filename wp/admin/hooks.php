<?php
namespace elasticsearch;

class Hooks
{
	function __construct()
	{
		add_action('wp_ajax_esreindex', array(&$this, 'reindex'));
		add_action('wp_ajax_esswap', array(&$this, 'swap'));
		add_action('save_post', array(&$this, 'save_post'));
		add_action('delete_post', array(&$this, 'delete_post'));
		add_action('trash_post', array(&$this, 'delete_post'));
		add_action('transition_post_status', array(&$this, 'transition_post'), 10, 3);
	}

	function save_post($post_id)
	{
		if (is_object($post_id)) {
			$post = $post_id;
		} else {
			$post = get_post($post_id);
		}

		if ($post == null || !in_array($post->post_type, Config::types())) {
			return;
		}

		if ($post->post_status == 'publish') {
			Indexer::addOrUpdate($post);
		} else {
			Indexer::delete($post);
		}
	}

	function transition_post($new_status, $old_status, $post)
	{
		if ($new_status != 'publish' && $new_status != $old_status) {
			Indexer::delete($post);
		}
	}

	function delete_post($post_id)
	{
		if (is_object($post_id)) {
			$post = $post_id;
		} else {
			$post = get_post($post_id);
		}

		if ($post == null || !in_array($post->post_type, Config::types())) {
			return;
		}

		Indexer::delete($post);
	}

	function swap()
	{
		try {
			$primary = Config::option('server_index');
			$secondary = Config::option('secondary_index');

			if ($secondary) {
				Config::set('server_index', $secondary);
				Config::set('secondary_index', $primary);
			}

			echo 1;
		} catch (\Exception $ex) {
			header("HTTP/1.0 500 Internal Server Error");

			echo $ex->getMessage();
		}

		die();
	}

	function reindex()
	{
		try {
			echo Indexer::reindex($_POST['page']);
		} catch (\Exception $ex) {
			header("HTTP/1.0 500 Internal Server Error");

			echo $ex->getMessage();
		}

		die();
	}
}

new Hooks();
?>
