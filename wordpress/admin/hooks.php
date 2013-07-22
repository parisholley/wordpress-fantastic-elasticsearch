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

		if(!in_array($post->post_type, Config::types())){
			return;
		}

		if ($post->post_status == 'trash'){
			Indexer::delete($post);
		}

		if ($post->post_status == 'publish'){
			Indexer::addOrUpdate($post);
		}
	}

	function delete_post( $post_id ) {
		if(is_object( $post_id )){
			$post = $post_id;
		} else {
			$post = get_post( $post_id );
		}

		if(!in_array($post->post_type, Config::types())){
			return;
		}

		Indexer::delete($post);
	}
}

new Hooks();
?>
