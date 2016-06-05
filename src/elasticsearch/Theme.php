<?php
namespace elasticsearch;

/**
 * This theme class provides utilities for introducing advanced functionality (such as ajax faceting) to your theme.
 *
 * @license http://opensource.org/licenses/MIT
 * @author Paris Holley <mail@parisholley.com>
 * @version 4.0.1
 **/
class Theme
{
	private static $selector;
	private static $instance;

	/**
	 * Tells wordpress to detect when ?esajax=1 is in the URL and remove header/footer so content can be replaced on page with AJX
	 **/
	public static function enableAjaxHooks()
	{
		if (isset($_GET['esasync'])) {
			add_action('get_header', function () {
				ob_start();
			});

			add_action('get_footer', array(__NAMESPACE__ . '\Theme', '_ajax_footer'));
		}
	}

	public static function setSelector($selector)
	{
		self::$selector = $selector;
	}

	public static function _ajax_footer()
	{
		$html = ob_get_contents();
		ob_clean();

		\phpQuery::newDocumentHTML($html);

		global $wp_query;

		$result = array(
			'content' => pq(self::$selector)->html(),
			'faceting' => Faceting::all(),
			'found' => $wp_query->found_posts
		);

		echo json_encode($result);

		if (!empty($_REQUEST)) {
			// only fail on webserver, not tests
			die();
		}
	}
}

?>
