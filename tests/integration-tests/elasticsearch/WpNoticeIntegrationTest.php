<?php
namespace elasticsearch;

class WpNoticeIntegrationTest extends BaseIntegrationTestCase
{
	public function test_save_post_found()
	{
		register_post_type('post');

		wp_insert_post(array(
			'post_type' => 'post',
			'post_status' => 'trash'
		));

		do_action('save_post', 1);
	}
}
?>