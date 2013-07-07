<?php
namespace elasticsearch;

class Indexer{
	static $types = array();

	static function per_page(){
		return apply_filters('es_indexer_per_page', 10);
	}

	static function get_posts($page = 1){
		$args = apply_filters('ex_indexer_get_posts', array(
			'posts_per_page' => self::per_page(),
			'post_type' => Api::types(),
			'paged' => $page,
			'post_status' => 'publish'
		));

		return get_posts($args);
	}

	static function get_count(){
		$query = new \WP_Query(array(
			'post_type' => Api::types(),
			'post_status' => 'publish'
		));

		return $query->found_posts; //performance risk?
	}

	static function build_document($post){
		$document = array();

		foreach(Api::fields() as $field){
			if($field == 'post_date'){
				$document[$field] = date('c',strtotime($post->$field));
			}else{
				$document[$field] = $post->$field;
			}
		}

		$taxes = array_intersect(Api::taxonomies(), get_object_taxonomies($post->post_type));

		foreach($taxes as $tax){
			$document[$tax] = array();

			foreach(wp_get_object_terms($post->ID, $tax) as $term){
				if(!in_array($term->slug, $document[$tax])){
					$document[$tax][] = $term->slug;
				}

				if($term->parent){
					$term = get_term($term->parent, $tax);
					
					while($term != null){
						if(!in_array($term->slug, $document[$tax])){
							$document[$tax][] = $term->slug;
						}

						if($term->parent){
							$term = get_term($term->parent, $tax);
						}else{
							$term = null;
						}
					}
				}
			}
		}
		
		return apply_filters('es_build_document', $document, $post);
	}

	static function map(){
		$numeric = Api::option('numeric');
		$index = Api::index(false);

		foreach(Api::fields() as $field){
			if($numeric[$field]){
				foreach(Api::types() as $type){
					$type = $index->getType($type);

					$mapping = new \Elastica_Type_Mapping($type);
					$mapping->setProperties(array($field => array(
						'type' => 'float'
					)));

					$mapping->send();
				}
			}

			if($field == 'post_date'){
				foreach(Api::types() as $type){
					$type = $index->getType($type);

					$mapping = new \Elastica_Type_Mapping($type);
					$mapping->setProperties(array($field => array(
						'type' => 'date'
					)));

					$mapping->send();
				}			
			}
		}
	}

	static function clear(){
		foreach(Api::types() as $type){
			$type = Api::index(true)->getType($type);

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
		$index = Api::index(true);

		$posts = self::get_posts($page);

		foreach($posts as $post){
			self::addOrUpdate($index, $post);
		}

		return count($posts);
	}

	static function delete($index, $post){
		if(!($type = self::$types[$post->post_type])){
			$type = self::$types[$post->post_type] = $index->getType($post->post_type);
		}

		$type->deleteById($post->ID);
	}

	static function addOrUpdate($index, $post){
		if(!($type = self::$types[$post->post_type])){
			$type = self::$types[$post->post_type] = $index->getType($post->post_type);
		}

		$data = self::build_document($post);

		$type->addDocument(new \Elastica_Document($post->ID, $data));		
	}
}
?>