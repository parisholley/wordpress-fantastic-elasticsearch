<?php
namespace elasticsearch;

class Api{
	static $client = null;
	static $index = null;
	static $options = null;

	static function option($name){
		if(self::$options == null){
			self::$options = get_option('elasticsearch');
		}

		return self::$options[$name];
	}

	static function score($type, $name){
		return self::option("score_{$type}_{$name}");
	}

	static function ranges($field){
		$config = Api::option($field . '_range');

		if($config){
			$ranges = array();

			foreach(split(',', $config) as $range){
				$ends = split('-', $range);

				$tmp = array();

				if($ends[0]){
					$tmp['from'] = $ends[0];
				}

				if($ends[1]){
					$tmp['to'] = $ends[1];
				}

				$ranges[$ends[0] . '-' . $ends[1]] = $tmp;
			}

			return $ranges;
		}

		return null;
	}

	static function client($write = false){
		if(self::$client == null){
			$settings = array(
				'url' => self::option('server_url')
			);
			
			if($write){
				$settings['timeout'] = self::option('server_timeout_write') ?: 300;
			}else{
				$settings['timeout'] = self::option('server_timeout_read') ?: 1;
			}

			self::$client = new \Elastica_Client($settings);
		}

		return self::$client;
	}

	static function index($write = false){
		if(self::$index == null){
			self::$index = self::client($write)->getIndex(self::option('server_index'));
		}

		return self::$index;
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