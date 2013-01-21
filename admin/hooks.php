<?php
namespace elasticsearch;

class Hooks{
	function __construct(){
		add_action( 'save_post', array( &$this, 'save_post' ) );
		add_action( 'delete_post', array( &$this, 'delete_post' ) );
		add_action( 'trash_post', array( &$this, 'delete_post' ) );
	}
	
	function save_post( $post_id ) {
		if(is_object( $post_id )){
			$post = $post_id;
		} else {
			$post = get_post( $post_id );
		}

		$index = Api::index(true);

		if ($post->post_status != 'publish'){
			Indexer::delete($index, $post);
		} else {
			Indexer::addOrUpdate($index, $post);
		}
	}

	function delete_post( $post_id ) {
		if(is_object( $post_id )){
			$post = $post_id;
		} else {
			$post = get_post( $post_id );
		}

		$index = Api::index(true);
		Indexer::delete($index, $post);
	}
}

new Hooks();
?>