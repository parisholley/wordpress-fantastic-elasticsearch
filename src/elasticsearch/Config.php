<?php
namespace elasticsearch;

class Config{
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
		$config = self::option($field . '_range');

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
}
?>
