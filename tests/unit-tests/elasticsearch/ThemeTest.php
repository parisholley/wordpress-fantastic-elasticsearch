<?php
namespace elasticsearch;

class ThemeTest extends BaseTestCase
{
	public function testAsyncRequest(){
		global $wp_query;

		$wp_query->facets = array();
		$wp_query->found_posts = 10;
		update_option('fields', array());

		$_GET['esasync'] = true;

		Theme::enableAjaxHooks();

		ob_start();

		do_action('get_header');

		echo '<div class="wrapper"><div class="content">wee<aside id="test">more</aside></div><div id="sidebar"></div></div>';

		Theme::setSelector('.content'); // set by sidebar widget

		do_action('get_footer');

		$html = ob_get_contents();
		ob_clean();

		$this->assertEquals('{"content":"wee<aside id=\"test\">more<\/aside>\n","faceting":[],"found":10}', $html);
		
		ob_end_clean();
	}
}
?>