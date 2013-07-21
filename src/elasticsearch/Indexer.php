<?php
namespace elasticsearch;

class Indexer{
	static function per_page(){
		return Config::apply_filters('indexer_per_page', 10);
	}

	static function get_posts($page = 1){
		$args = Config::apply_filters('indexer_get_posts', array(
			'posts_per_page' => self::per_page(),
			'post_type' => Config::types(),
			'paged' => $page,
			'post_status' => 'publish'
		));

		return get_posts($args);
	}

	static function get_count(){
		$query = new \WP_Query(array(
			'post_type' => Config::types(),
			'post_status' => 'publish'
		));

		return $query->found_posts; //performance risk?
	}

	static function build_document($post){
		$document = array();

		foreach(Config::fields() as $field){
			if(isset($post->$field)){
				if($field == 'post_date'){
					$document[$field] = date('c',strtotime($post->$field));
				}else{
					$document[$field] = $post->$field;
				}
			}
		}

		if(isset($post->post_type)){
			$taxes = array_intersect(Config::taxonomies(), get_object_taxonomies($post->post_type));

			foreach($taxes as $tax){
				$document[$tax] = array();

				foreach(wp_get_object_terms($post->ID, $tax) as $term){
					if(!in_array($term->slug, $document[$tax])){
						$document[$tax][] = $term->slug;
					}

					if(isset($term->parent) && $term->parent){
						$parent = get_term($term->parent, $tax);
						
						while($parent != null){
							if(!in_array($parent->slug, $document[$tax])){
								$document[$tax][] = $parent->slug;
							}

							if(isset($parent->parent) && $parent->parent){
								$parent = get_term($parent->parent, $tax);
							}else{
								$parent = null;
							}
						}
					}
				}
			}
		}
		
		return Config::apply_filters('indexer_build_document', $document, $post);
	}

	static function map(){
		$numeric = Config::option('numeric');
		$index = Indexer::index(false);

		foreach(Config::fields() as $field){
			$estype = 'string';

			if(isset($numeric[$field])){
				$estype = 'float';
			}elseif($field == 'post_date'){
				$estype = 'date';
			}

			foreach(Config::types() as $type){
				$type = $index->getType($type);

				$mapping = new \Elastica\Type\Mapping($type);
				$mapping->setProperties(array($field => array(
					'type' => $estype
				)));

				$mapping->send();
			}
		}
	}

	static function clear(){
		foreach(Config::types() as $type){
			$type = Indexer::index(true)->getType($type);

			try{
				$type->delete();
			}catch(\Exception $ex){
				// no way to detect if type exists
				if(strpos($ex->getMessage(), 'TypeMissingException') === false){
					throw $ex;
				}
			}
		}

		self::map();
	}

	static function reindex($page = 1){
		$index = Indexer::index(true);

		$posts = self::get_posts($page);

		foreach($posts as $post){
			self::addOrUpdate($index, $post);
		}

		return count($posts);
	}

	static function delete($post){
		$index = self::index(true);

		$type = $index->getType($post->post_type);

		$type->deleteById($post->ID);
	}

	static function addOrUpdate($index, $post){
		$type = $index->getType($post->post_type);

		$data = self::build_document($post);

		$type->addDocument(new \Elastica\Document($post->ID, $data));		
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
}
?>