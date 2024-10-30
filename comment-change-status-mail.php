<?php

function comment_change_status__comment_post( $comment_id, $comment_approved, $rejected = false ) {
	global $wpdb, $comment_change_status__url;
	
	
	if ( $destinationMail = get_option('change-comment-status-mail') ) {
		// Usamos la opcion
	}else{
		// Usamos el administrador
		$destinationMail = get_option('admin_email');
	}
	
	if ( !empty($destinationMail) ) {
		
		if ( $comment_id > 0 ) {
			
			$comment_new_hash = wp_generate_password(30, false);
			
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE $wpdb->comments SET comment_hash = %s WHERE comment_ID = %d",
					$comment_new_hash,
					$comment_id
				)
			);
			
			if ( $wpdb->rows_affected == 1 && ( $comment_approved == '1' || $comment_approved == '0' ) ) {
				
				$comment = get_comment($comment_id);
				$post    = get_post($comment->comment_post_ID);
				$user    = get_userdata( $post->post_author );
				
				$comment_author_domain = @gethostbyaddr($comment->comment_author_IP);
				
				$blogname = get_option('blogname');
				
				$notify_message = '';
				
				if ( $comment_approved == '0' ) {
					
					$notify_message  = sprintf( __('COMMENT UNAPPROVED on your post #%1$s "%2$s"', 'comment-change-status'), $comment->comment_post_ID, $post->post_title ) . "\r\n";
					$notify_message .= sprintf( __('Author : %1$s (IP: %2$s , %3$s)', 'comment-change-status'), $comment->comment_author, $comment->comment_author_IP, $comment_author_domain ) . "\r\n";
					$notify_message .= sprintf( __('E-mail : %s', 'comment-change-status'), $comment->comment_author_email ) . "\r\n";
					$notify_message .= sprintf( __('URL    : %s', 'comment-change-status'), $comment->comment_author_url ) . "\r\n";
					$notify_message .= sprintf( __('Whois  : http://ws.arin.net/cgi-bin/whois.pl?queryinput=%s', 'comment-change-status'), $comment->comment_author_IP ) . "\r\n";
					$notify_message .= __('Comment: ', 'comment-change-status') . "\r\n" . $comment->comment_content . "\r\n\r\n";
					$notify_message .= __('You can see all comments on this post here: ', 'comment-change-status') . "\r\n";
					
					$notify_message .= "\t".get_permalink($comment->comment_post_ID) . "#comments\r\n\r\n";
					$notify_message .= "\t".sprintf( __('Approve it: %s', 'comment-change-status'), $comment_change_status__url . "/comment-change-status-check.php?action=approve&becid=".$comment_new_hash) . "\r\n";
					
					$subject = sprintf( __('[%1$s] Comment Unapproved: "%2$s"', 'comment-change-status'), $blogname, $post->post_title );
					
				}elseif ( $comment_approved == '1' ) {
					
					$notify_message  = sprintf( __('COMMENT APPROVED on your post #%1$s "%2$s"', 'comment-change-status'), $comment->comment_post_ID, $post->post_title ) . "\r\n";
					$notify_message .= sprintf( __('Author : %1$s (IP: %2$s , %3$s)', 'comment-change-status'), $comment->comment_author, $comment->comment_author_IP, $comment_author_domain ) . "\r\n";
					$notify_message .= sprintf( __('E-mail : %s', 'comment-change-status'), $comment->comment_author_email ) . "\r\n";
					$notify_message .= sprintf( __('URL    : %s', 'comment-change-status'), $comment->comment_author_url ) . "\r\n";
					$notify_message .= sprintf( __('Whois  : http://ws.arin.net/cgi-bin/whois.pl?queryinput=%s', 'comment-change-status'), $comment->comment_author_IP ) . "\r\n";
					$notify_message .= __('Comment: ', 'comment-change-status') . "\r\n" . $comment->comment_content . "\r\n\r\n";
					$notify_message .= __('You can see all comments on this post here: ', 'comment-change-status') . "\r\n";
					
					$notify_message .= "\t".get_permalink($comment->comment_post_ID) . "#comments\r\n\r\n";
					$notify_message .= "\t".sprintf( __('Unapprove it: %s', 'comment-change-status'), $comment_change_status__url . "/comment-change-status-check.php?action=unapprove&becid=".$comment_new_hash) . "\r\n";
					
					$subject = sprintf( __('[%1$s] Comment Approved: "%2$s"', 'comment-change-status'), $blogname, $post->post_title );
					
				}
				
				/* 30.11.2009 - Options to delete and mark to spam */
				$notify_message .= "\n\t".sprintf( __('Delete it: %s', 'comment-change-status'), $comment_change_status__url . "/comment-change-status-check.php?action=deleteit&becid=".$comment_new_hash) . "\r\n";
				if ( $comment_approved != 'spam' ) $notify_message .= "\n\t".sprintf( __('Spam it: %s', 'comment-change-status'), $comment_change_status__url . "/comment-change-status-check.php?action=spamit&becid=".$comment_new_hash) . "\r\n";
				
				if ( $notify_message ) {
					
					$wp_email = 'wordpress@' . preg_replace('#^www\.#', '', strtolower($_SERVER['SERVER_NAME']));
					
					if ( '' == $comment->comment_author ) {
						$from = "From: \"$blogname\" <$wp_email>";
						if ( '' != $comment->comment_author_email )
							$reply_to = "Reply-To: $comment->comment_author_email";
					} else {
						$from = "From: \"$comment->comment_author\" <$wp_email>";
						if ( '' != $comment->comment_author_email )
							$reply_to = "Reply-To: \"$comment->comment_author_email\" <$comment->comment_author_email>";
					}
					
					$message_headers = "$from\n"
						. "Content-Type: text/plain; charset=\"" . get_option('blog_charset') . "\"\n";
					
					if ( isset($reply_to) )
						$message_headers .= $reply_to . "\n";
					
					@wp_mail($destinationMail, $subject, $notify_message, $message_headers);
				}
				
			}
		}
		
	}
}
add_action('comment_post', 'comment_change_status__comment_post', 10, 2);

?>
