<?php
namespace elasticsearch;

class Indexer{
	static $types = array();

	static function get_posts(){
		return get_posts(Defaults::get_posts_args());
	}

	static function build_document($post){
		$document = array();

		foreach(Api::fields() as $field){
			$document[$field] = $post->$field;
		}

		$taxes = array_intersect(Api::taxonomies(), get_object_taxonomies($post->post_type));

		foreach($taxes as $tax){
			$document[$tax] = array();

			foreach(wp_get_object_terms($post->ID, $tax) as $term){
				$document[$tax][] = $term->slug;
			}
		}

		return apply_filters('es_build_document', $document, $post);
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
	}

	static function reindex(){
		$index = Api::index(true);

		foreach(self::get_posts() as $post){
			self::addOrUpdate($index, $post);
		}
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