<?php
namespace elasticsearch;

/**
* This class handles the magic of building documents and sending them to ElasticSearch for indexing.
*
* @license http://opensource.org/licenses/MIT
* @author Paris Holley <mail@parisholley.com>
* @version 2.0.0
**/
class Indexer{
	/**
	* The number of posts to index per page when re-indexing
	*
	* @return integer posts per page
	**/
	static function per_page(){
		return Config::apply_filters('indexer_per_page', 10);
	}

	/**
	* Retrieve the posts for the page provided
	*
	* @param integer $page The page of results to retrieve for indexing
	*
	* @return WP_Post[] posts
	**/
	static function get_posts($page = 1){
		$args = Config::apply_filters('indexer_get_posts', array(
			'posts_per_page' => self::per_page(),
			'post_type' => Config::types(),
			'paged' => $page,
			'post_status' => 'publish'
		));

		return get_posts($args);
	}

	/**
	* Retrieve count of the number of posts available for indexing
	*
	* @return integer number of posts
	**/
	static function get_count(){
		$query = new \WP_Query(array(
			'post_type' => Config::types(),
			'post_status' => 'publish'
		));

		return $query->found_posts; //performance risk?
	}

	/**
	* Removes all data in the ElasticSearch index
	**/
	static function clear(){
		foreach(Config::types() as $type){
			$index = self::_index(true);
			$mapping = $index->getMapping();

			if(isset($mapping[Config::option('server_index')])){
				foreach($mapping[Config::option('server_index')] as $type => $props){
					$index->getType($type)->delete();
				}
			}
		}

		self::_map();
	}

	/**
	* Re-index the posts on the given page in the ElasticSearch index
	*
	* @param integer $page The page to re-index
	**/
	static function reindex($page = 1){
		$index = self::_index(true);

		$posts = self::get_posts($page);

		foreach($posts as $post){
			self::addOrUpdate($post);
		}

		return count($posts);
	}

	/**
	* Removes a post from the ElasticSearch index
	*
	* @param WP_Post $post The wordpress post to remove
	**/
	static function delete($post){
		$index = self::_index(true);

		$type = $index->getType($post->post_type);

		try{
			$type->deleteById($post->ID);
		}catch(\Elastica\Exception\NotFoundException $ex){
			// ignore
		}
	}

	/**
	* Updates an existing document in the ElasticSearch index (or creates it if it doesn't exist)
	*
	* @param WP_Post $post The wordpress post to remove
	**/
	static function addOrUpdate($post){
		$type = self::_index(true)->getType($post->post_type);

		$data = self::_build_document($post);

		$type->addDocument(new \Elastica\Document($post->ID, $data));		
	}

	/**
	* Reads F.E.S configuration and updates ElasticSearch field mapping information (this can corrupt existing data).
	* @internal
	**/
	static function _map(){
		$numeric = Config::option('numeric');
		$notanalyzed = Config::option('not_analyzed');

		$index = self::_index(false);

		foreach(Config::taxonomies() as $tax){
			$props = array(
				'type' => 'string',
				'index' => 'not_analyzed'
			);

			$props = Config::apply_filters('indexer_map_taxonomy', $props, $tax);

			$propsname = array(
				'type' => 'string'
			);

			$propsname = Config::apply_filters('indexer_map_taxonomy_name', $propsname, $tax);

			foreach(Config::types() as $type){
				$type = $index->getType($type);

				$mapping = new \Elastica\Type\Mapping($type);
				$mapping->setProperties(array($tax => $props));
				$mapping->send();

				$mapping = new \Elastica\Type\Mapping($type);
				$mapping->setProperties(array($tax . '_name' => $propsname));
				$mapping->send();
			}			
		}

		foreach(Config::fields() as $field){
			$props = array(
				'type' => 'string'
			);

			if(isset($numeric[$field])){
				$props['type'] = 'float';
			}elseif($field == 'post_date'){
				$props['type'] = 'date';
				$props['format'] = 'date_time_no_millis';
			}elseif(isset($notanalyzed[$field])){
				$props['index'] = 'not_analyzed';
			}else{
				$props['index'] = 'analyzed';
			}

			$props = Config::apply_filters('indexer_map_field', $props, $field);

			foreach(Config::types() as $type){
				$type = $index->getType($type);

				$mapping = new \Elastica\Type\Mapping($type);
				$mapping->setProperties(array($field => $props));

				$mapping->send();
			}
		}
    self::_map_meta_values($index);
	}

	/**
	* Takes a wordpress post object and converts it into an associative array that can be sent to ElasticSearch
	*
	* @param WP_Post $post wordpress post object
	* @return array document data
	* @internal
	**/
	static function _build_document($post){
		global $blog_id;
		$document = array( 'blog_id' => $blog_id );
    $document = self::_build_field_values($post, $document);
    $document = self::_build_meta_values($post, $document);
    $document = self::_build_tax_values($post, $document);
		return Config::apply_filters('indexer_build_document', $document, $post);
	}

  /**
   * Add post meta values to elasticsearch object, only if they are present.
   *
   * @param WP_Post $post
   * @param Array $document to write to es
   * @return Array $document
   * @internal
   **/
  static function _build_meta_values($post, $document){
    $meta_fields = array_intersect(Config::meta_fields(), get_post_custom_keys($post->ID));
    foreach($meta_fields as $field){
      $val = get_post_meta($post->ID, $field, true);
      if(isset($val))
        $document[$field] = $val;
    }
    return $document;
  }

  /**
   * Add post fields to new elasticsearch object, if the field is set
   *
   * @param WP_Post $post
   * @param Array $document to write to es
   * @return Array $document
   * @internal
   **/
  static function _build_field_values($post, $document){
    foreach(Config::fields() as $field){
      if(isset($post->$field)){
        if($field == 'post_date'){
          $document[$field] = date('c',strtotime($post->$field));
        }else if($field == 'post_content'){
          $document[$field] = strip_tags($post->$field);
        }else{
          $document[$field] = $post->$field;
        }
      }
    }
    return $document;
  }

  /**
   * Add post taxonomies to elasticsearch object
   *
   * @param WP_Post $post
   * @param Array $document to write to es
   * @return Array $document
   * @internal
   **/
  static function _build_tax_values($post, $document){

    if(!isset($post->post_type))
      return;

    $taxes = array_intersect(Config::taxonomies(), get_object_taxonomies($post->post_type));
    foreach($taxes as $tax){
      $document[$tax] = array();

      foreach(wp_get_object_terms($post->ID, $tax) as $term){
        if(!in_array($term->slug, $document[$tax])){
          $document[$tax][] = $term->slug;
          $document[$tax . '_name'][] = $term->name;
        }

        if(isset($term->parent) && $term->parent){
          $parent = get_term($term->parent, $tax);

          while($parent != null){
            if(!in_array($parent->slug, $document[$tax])){
              $document[$tax][] = $parent->slug;
              $document[$tax . '_name'][] = $parent->name;
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
    return $document;
  }

  /**
   * Add post meta values type definitions to elasticsearch.
   * @param Elastica\Index $index to
   * @internal
   **/
  static function _map_meta_values(&$index){

    $numeric = Config::option('numeric');
    $notanalyzed = Config::option('not_analyzed');

    foreach(Config::meta_fields() as $field){
      $props = array(
        'type' => 'string'
      );

      if(isset($numeric[$field])){
        $props['type'] = 'float';
      }elseif(isset($notanalyzed[$field])){
        $props['index'] = 'not_analyzed';
      }else{
        $props['index'] = 'analyzed';
      }

      $props = Config::apply_filters('indexer_map_meta_field', $props, $field);

      foreach(Config::types() as $type){
        $type = $index->getType($type);

        $mapping = new \Elastica\Type\Mapping($type);
        $mapping->setProperties(array($field => $props));

        $mapping->send();
      }
    }
  }

	/**
	* The Elastica\Client object used by F.E.S
	*
	* @param boolean $write Specifiy whether you are making read-only or write transactions (currently just adjusts timeout values)
	*
	* @return Elastica\Client
	* @internal
	**/
	static function _client($write = false){
		$settings = array(
			'url' => Config::option('server_url')
		);
		
		if($write){
			$settings['timeout'] = Config::option('server_timeout_write') ?: 300;
		}else{
			$settings['timeout'] = Config::option('server_timeout_read') ?: 1;
		}

		return new \Elastica\Client($settings);
	}

	/**
	* The Elastica\Index object used by F.E.S
	*
	* @param boolean $write Specifiy whether you are making read-only or write transactions (currently just adjusts timeout values)
	*
	* @return Elastica\Index
	* @internal
	**/
	static function _index($write = false){
		return self::_client($write)->getIndex(Config::option('server_index'));
	}
}
?>
