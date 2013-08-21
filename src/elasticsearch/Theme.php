<?php
namespace elasticsearch;

/**
* This theme class provides utilities for introducing advanced functionality (such as ajax faceting) to your theme.
*
* @license http://opensource.org/licenses/MIT
* @author Paris Holley <mail@parisholley.com>
* @version 2.1.0
**/
class Theme{
	private static $ajaxStart;
	private static $ajaxEnd;
	private static $instance;

	/**
	* Tells wordpress to detect when ?esajax=1 is in the URL and remove header/footer so content can be replaced on page with AJX
	**/
	public static function enableAjaxHooks(){
		if(isset($_GET['esasync'])){
			add_action('get_header', function(){
				ob_start();
			});

			add_action('get_footer', array(__NAMESPACE__ .'\Theme', '_ajax_footer'));
		}
	}

	public static function setAjaxStart($start){
		self::$ajaxStart = $start;
	}

	public static function setAjaxStop($end){
		self::$ajaxEnd = $end;
	}

	public static function _ajax_footer(){
		$html = ob_get_contents();
		ob_clean();

		$split = explode(self::$ajaxStart, $html);

		if(count($split) != 2){
			echo $html;
			return;
		}

		$split = explode(self::$ajaxEnd, $split[1]);
	
		if(count($split) != 2){
			echo $html;
			return;
		}

		$result = array(
			'content' => $split[0],
			'faceting' => Faceting::all()
		);

		echo json_encode($result);

		die();	
	}
}
?>
