<?php
namespace elasticsearch;

class WpNoticeTest extends BaseTestCase
{
	public function test_delete_post_notfound()
	{
		register_post_type('post');

		do_action('delete_post', 1);
	}

	public function test_trash_post_notfound()
	{
		register_post_type('post');

		do_action('trash_post', 1);
	}

	public function test_save_post_notfound()
	{
		register_post_type('post');

		do_action('save_post', 1);
	}

	public function test_pre_get_posts_notmainquery(){
		$query = new \WP_Query();
		$query->is_main_query = false;

		do_action('pre_get_posts', $query);
	}

	public function test_pre_get_posts_taxonomyId(){
		$query = new \WP_Query();
		$query->is_main_query = true;
		$query->query_vars['cat'] = 1;

		TestContext::$is['is_search'] = true;
		TestContext::$is['is_admin'] = false;
		TestContext::$is['is_tax'] = false;

		update_option('enable_categories', array(1));

		wp_insert_term('Category 1', 'category', array('slug' => 'cat1'));

		do_action('pre_get_posts', $query);
	}

	public function test_pre_get_posts_taxonomyName(){
		$query = new \WP_Query();
		$query->is_main_query = true;
		$query->query_vars['category_name'] = 'cat1';
		$query->query_vars['posts_per_page'] = 10;

		TestContext::$is['is_search'] = true;
		TestContext::$is['is_admin'] = false;
		TestContext::$is['is_tax'] = false;

		update_option('enable_categories', array(1));

		wp_insert_term('Category 1', 'category', array('slug' => 'cat1'));

		do_action('pre_get_posts', $query);

		do_action('the_posts', array());
	}

	public function test_pre_get_posts_search(){
		$query = new \WP_Query();
		$query->is_main_query = true;
		$query->query_vars['s'] = 'term';
		$query->query_vars['posts_per_page'] = 10;

		TestContext::$is['is_search'] = true;
		TestContext::$is['is_admin'] = false;
		TestContext::$is['is_tax'] = false;

		update_option('enable', 1);

		wp_insert_term('Category 1', 'category', array('slug' => 'cat1'));

		do_action('pre_get_posts', $query);

		do_action('the_posts', array());
	}

	public function test_init(){
		register_post_type('post');
		register_taxonomy('tax1', 'post');
		update_option('numeric', array('field1' => 1));

		do_action('init');
		do_action('admin_menu');
		do_action('admin_print_styles-');

		ob_start();
		do_action('admin_init');
		ob_end_clean();
	}
}
?>