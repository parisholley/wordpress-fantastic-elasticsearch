<?php
namespace elasticsearch;

/**
 * Find related documents - yet another related post using ES more-like-this api
 * http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-more-like-this.html
 *
 * Before one can use the mlt feature the mlt_fields used (e.g post_content, post_title) must have at least one of the
 * following mapping-attributes enabled:
 * - store=>'yes'
 * - term_vector =>'yes'
 *
 * Those settings lead to a bigger index(depending on your content term_vector probably less) so we dont set them for
 * all fields. Use a hook in functions.php to define the needed fields and wipe the es-index from the admin interface
 * to re-index with the new mappings.
 *    # prio = 1, accepted args = 3
 *    add_filter('elasticsearch_indexer_map', 'add_elasticsearch_mapping', 1, 3);
 *
 *    function add_elasticsearch_mapping($props, $field, $kind){
 *      $mlt_fields = array('post_title', 'post_content', 'post_tag_name', 'category_name');
 *      if(in_array($field, $mlt_fields)){
 *        //$props['store'] = 'yes';
 *        $props['term_vector'] = 'yes';
 *      }
 *      return $props;
 *    }
 *
 * See http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/mapping-core-types.html
 *
 * @license http://opensource.org/licenses/MIT
 * @author Georg Leciejewski
 * @version 2.0.0
 **/
class Related{
  /**
   * @param Object $post  Wordpress Post
   * @param array $args
   *        array $args['mlt'] more-like-this params merged with defaults
   *        array $args['query'] passed on to ES query
   */
  public static function search($post, $args=array()){
    global $blog_id;

    if(!isset($args['mlt']))
      $args['mlt'] = array();
    if(!isset($args['query']))
      $args['query'] = array();
    if(!isset($args['filter']))
      $args['filter'] = array();

    // always scope to current blog
    $args['query']['filter']['bool']['must'][] = array( 'term' => array( 'blog_id' => $blog_id ) );

    $query =new \Elastica\Query($args['query']);
    $query->setFields(array('id'));
    // Return just the similar with same tag
//    $filterTerm = new \Elastica\Query\Term();
//  $filterTerm->setTerm('tag_name', 'my tag name' );
//  $query->setFilter($filterTerm);

    try{
      $index = Indexer::_index(false);
      $type = new \Elastica\Type($index, $post->post_type);
      $document = $type->getDocument($post->ID);
      // Return all similar by title, body
      $mlt_params = array_merge(self::_mlt_defaults(), $args['mlt']);
      $response = $type->moreLikeThis($document, $mlt_params, $query);

      return self::_parseResults($response);
    }catch(\Exception $ex){
      error_log($ex);
      return null;
    }
  }

  /**
   * @internal
   **/
  public static function _parseResults($response){
    $ids = array();
    foreach($response->getResults() as $result){
      $ids[] = (int)$result->getId();
    }
    return $ids;
  }

  /**
   * Default more-like-this API query params
   * @return array
   */
  public static function _mlt_defaults(){
    return array(
      'search_size' => 5,
      'min_term_freq' => '2',
      'min_doc_freq' => '1',
      'min_word_len' => '3',
      // CAUTION NO space between fields comma
      'mlt_fields'=>'post_title,post_content'
    );
  }

  /**
   *
   * @param $post
   * @param array $args
   *        array $args['mlt'] more-like-this params merged with defaults
   *        array $args['query'] passed on to ES query
   * @return null|\WP_Query
   */
  function do_search($post, $args=array()){

    $post_ids = self::search($post, $args);
    if($post_ids == null || empty($post_ids) ){
      return null;
    }
    $this->ids = $post_ids;
    $wp_query_args = array(
      'showposts' => count($post_ids),
      'post__in' => $post_ids,
      'post_type' => Config::types(),
      'post_status' => 'publish'
    );
    $wp_query= new \WP_Query();
    $wp_query->query($wp_query_args);
    usort($wp_query->posts, array(&$this, 'sort_posts'));
    return $wp_query;
  }

  function sort_posts($a, $b){
    return array_search($b->ID, $this->ids) > array_search($a->ID, $this->ids) ? -1 : 1;
  }

}