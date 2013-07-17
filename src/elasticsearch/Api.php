<?php
namespace elasticsearch;

class Api{
	static $options = null;

	static function option($name){
		if(self::$options == null){
			self::$options = &get_option('elasticsearch');
		}

		return isset(self::$options[$name]) ? self::$options[$name] : null;
	}

	static function score($type, $name){
		return self::option("score_{$type}_{$name}");
	}

	static function ranges($field){
		$config = Api::option($field . '_range');

		if($config){
			$ranges = array();

			foreach(explode(',', $config) as $range){
				$ends = explode('-', $range);

				$tmp = array();

				if(is_numeric($ends[0])){
					$tmp['from'] = $ends[0];
				}

				if(is_numeric($ends[1])){
					$tmp['to'] = $ends[1];
				}

				$ranges[$ends[0] . '-' . $ends[1]] = $tmp;
			}

			return $ranges;
		}

		return null;
	}

	static function client($write = false){
		$settings = array(
			'url' => self::option('server_url')
		);
		
		if($write){
			$settings['timeout'] = self::option('server_timeout_write') ?: 300;
		}else{
			$settings['timeout'] = self::option('server_timeout_read') ?: 1;
		}

		return new \Elastica\Client($settings);
	}

	static function index($write = false){
		return self::client($write)->getIndex(self::option('server_index'));
	}

	static function apply_filters(){
		$args = func_get_args();
		$args[0] = 'elasticsearch_' . $args[0];

		return call_user_func_array('apply_filters', $args);
	}

	static function fields(){
		$fieldnames = Defaults::fields();

		if($fields = self::option('fields')){
			$fieldnames = array_keys($fields);
		}

		return apply_filters('es_api_fields', $fieldnames);
	}

	static function facets(){
		return self::taxonomies();
	}

	static function types(){
		$types = self::option('types');

		if($types){
			return array_keys($types);
		}

		return Defaults::types();
	}

	static function taxonomies(){
		$taxes = self::option('taxonomies');

		if($taxes){
			return array_keys($taxes);
		}

		return Defaults::taxonomies(self::types());
	}

	static function parse_query($str) {
	global $NHP_Options;
	$relation = $NHP_Options->get('multiple_relation');
	  # result array
	  $arr = array();

	  # split on outer delimiter
	  $pairs = explode('&', $str);

	  # loop through each pair
	  foreach ($pairs as $i) {
	    # split into name and value
	    list($elasticsearch,$value) = explode('=', $i, 2);
	    list($name,$key) = explode(urlencode($relation), $elasticsearch, 2);
	    $arr[$name][] = $value;
	  }

	  # return result array
	  return $arr;
	}
}
?>
