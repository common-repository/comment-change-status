<?php

### Load WP-Config File If This File Is Called Directly
if (!function_exists('add_action')) {
	$wp_root = '../../..';
	
	if (file_exists($wp_root.'/wp-load.php')) {
		require_once($wp_root.'/wp-load.php');
	} else {
		require_once($wp_root.'/wp-config.php');
	}
}


### Use WordPress 2.6 Constants
if ( !defined('WP_CONTENT_DIR') )
	define( 'WP_CONTENT_DIR', ABSPATH.'wp-content');
if ( !defined('WP_CONTENT_URL') )
	define('WP_CONTENT_URL', get_option('siteurl').'/wp-content');

// Cogemos la ruta
$comment_change_status__wp_dirname = basename(dirname(dirname(__FILE__))); // for "plugins" or "mu-plugins"
$comment_change_status__pi_dirname = basename(dirname(__FILE__)); // plugin name

$comment_change_status__path = WP_CONTENT_DIR.'/'.$comment_change_status__wp_dirname.'/'.$comment_change_status__pi_dirname;
$comment_change_status__url = WP_CONTENT_URL.'/'.$comment_change_status__wp_dirname.'/'.$comment_change_status__pi_dirname;


require_once('comment-change-status-mail.php');


if ( !function_exists('comment_change_status__clean_cache_post') ) :
	function comment_change_status__clean_cache_post( $comment_id ) {
		
		global $wpdb;
		
		// Check IP From IP Logging Database
		$post_id = $wpdb->get_var("
			SELECT comment_post_ID
			FROM $wpdb->comments
			WHERE comment_ID = '".$commment_id."'
		");
		
		// Si tenemos el WP-Super-Cache, lo limpiamos...
		if ( function_exists('wp_cache_post_edit') ) wp_cache_post_edit($post_id);
		// Si no esta, es posible que tengamos WP Cache ...
		else if ( function_exists('wp_cache_post_change') ) wp_cache_post_change($post_id);
		
	}
endif;


if ( isset($_GET['becid']) && $_GET['becid'] != '' ) {
	
	function comment_change_status__process() {
		global $wpdb;
		
		// Check IP From IP Logging Database
		$get_comment_by_md5ID = $wpdb->get_var("
			SELECT comment_ID
			FROM $wpdb->comments
			WHERE comment_hash = '".$_GET['becid']."'
		");
		
		if ( $get_comment_by_md5ID > 0 ) {
			
			$message = __('Hi!?!', 'comment-change-status');
			
			switch ( $_GET['action'] ) :
				
				case 'approve':
					$wpdb->query(
						$wpdb->prepare(
							"UPDATE $wpdb->comments SET comment_hash = %s, comment_approved = 1 WHERE comment_ID = %d",
							0,
							$get_comment_by_md5ID
						)
					);
					
					comment_change_status__clean_cache_post( $get_comment_by_md5ID );
					
					$message = __('The comment has been approved.', 'comment-change-status');
					
					break;
				
				case 'unapprove':
					$wpdb->query(
						$wpdb->prepare(
							"UPDATE $wpdb->comments SET comment_hash = %s, comment_approved = 0 WHERE comment_ID = %d",
							0,
							$get_comment_by_md5ID
						)
					);
					
					comment_change_status__clean_cache_post( $get_comment_by_md5ID );
					
					$message = __('The comment has been unapproved.', 'comment-change-status');
					
					break;
				
				case 'spamit':
					$wpdb->query(
						$wpdb->prepare(
							"UPDATE $wpdb->comments SET comment_hash = %s, comment_approved = 'spam' WHERE comment_ID = %d",
							0,
							$get_comment_by_md5ID
						)
					);
					
					comment_change_status__clean_cache_post( $get_comment_by_md5ID );
					
					$message = __('The comment has been spam it.', 'comment-change-status');
					
					break;
				
				case 'deleteit':
					if ( wp_delete_comment($get_comment_by_md5ID) ) {
						comment_change_status__clean_cache_post( $get_comment_by_md5ID );
						$message = __('The comment has been deleted.', 'comment-change-status');
					}else{
						$message = __('The comment not has been deleted (ERROR).', 'comment-change-status');
					}
					
					break;
				
			endswitch;
			
			//comment_change_status__comment_post( $get_comment_by_md5ID, 1, true );
			
			header('Content-Type: text/html; charset='.get_option('blog_charset').'');
			die($message);
			//wp_die(__('The comment has been unapproved.', 'comment-change-status'), '', 'response=200');
		}
		
		die('fin');
	}
	comment_change_status__process();
}

header('Content-Type: text/html; charset='.get_option('blog_charset').'');
die(__('The comment has been yet visited, previosly?! ;-)', 'comment-change-status'));


?>
