<?php
namespace elasticsearch;

class Hooks{
	function __construct(){
		add_action( 'save_post', array( &$this, 'save_post' ) );
		add_action( 'delete_post', array( &$this, 'delete_post' ) );
		add_action( 'trash_post', array( &$this, 'delete_post' ) );
		add_action( 'personal_options_update', array( &$this, 'update_author' ) );
		add_action( 'edit_user_profile_update', array( &$this, 'update_author' ) );
	}
	
	function save_post( $post_id, $display_name = null ) {
		if(is_object( $post_id )){
			$post = $post_id;
		} else {
			$post = get_post( $post_id );
		}

		if(!in_array($post->post_type, Api::types())){
			return;
		}

		$index = Api::index(true);

		if ($post->post_status == 'trash'){
			Indexer::delete($index, $post);
		}

		if ($post->post_status == 'publish'){
			Indexer::addOrUpdate($index, $post, $display_name);
		}
	}

	function delete_post( $post_id ) {
		if(is_object( $post_id )){
			$post = $post_id;
		} else {
			$post = get_post( $post_id );
		}

		if(!in_array($post->post_type, Api::types())){
			return;
		}

		$index = Api::index(true);
		Indexer::delete($index, $post);
	}

	function update_author($user_id){
		global $es_hooks;

		$query = new \WP_Query("author=$user_id&posts_per_page=-1");
		
		if ( $query->have_posts() ) {
			// Avoid an infinite loop
			if(remove_action('save_post', array($es_hooks, 'save_post'))){

				while ( $query->have_posts() ) {
					$query->the_post();
					$this->save_post(get_the_ID(), $_POST['display_name']);
				}

				// Hook the action back up
				add_action( 'save_post', array( &$this, 'save_post' ) );
				
			}
		}
	}
}

$es_hooks = new Hooks();
?>
