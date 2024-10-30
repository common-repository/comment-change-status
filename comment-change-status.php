<?php
/*
Plugin Name: Comment Change Status
Plugin URI: http://taller.pequelia.es/plugins/comment-change-status/
Description: Change comment status with one only click on e-mail
Version: 0.10.1
Author: Alejandro Carravedo (Blogestudio)
Author URI: http://blogestudio.com/

0.9.0
	- Primera Beta del Plugin
0.9.1
	- Arreglado README.TXT
0.10.0
	- Añadidas opciones en los correos para "Borrar" y "Marcar como SPAM" los comentarios.
0.10.1
	- Separadas algo mas las opciones
	- Solo muestra la opcion de "Marcar como SPAM" si el correo YA NO LO ES.
*/


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


### Create Text Domain For Translations
function comment_change_status__init() {
	global $comment_change_status__pi_dirname;
	
	// Load the location file
	load_plugin_textdomain('comment-change-status', false, $comment_change_status__pi_dirname.'/langs');
}
add_action('init', 'comment_change_status__init');


function comment_change_status__activate() {
	global $wpdb;
	
	if(@is_file(ABSPATH.'/wp-admin/upgrade-functions.php')) {
		include_once(ABSPATH.'/wp-admin/upgrade-functions.php');
	} elseif(@is_file(ABSPATH.'/wp-admin/includes/upgrade.php')) {
		include_once(ABSPATH.'/wp-admin/includes/upgrade.php');
	} else {
		die('We have problem finding your \'/wp-admin/upgrade-functions.php\' and \'/wp-admin/includes/upgrade.php\'');
	}
	
	maybe_add_column(
		$wpdb->comments,
		'comment_hash',
		"ALTER TABLE $wpdb->comments ADD comment_hash varchar(35) NOT NULL DEFAULT '-1';"
	);
	
}
register_activation_hook( __FILE__,'comment_change_status__activate');



/* Opciones del formulario de discusion */
function comment_change_status__admin_init() {
	
	register_setting('discussion', 'change-comment-status-mail');
	
	add_settings_field(
		'change-comment-status-mail',
		__('Change Comment Status', 'comment-change-status'),
		'comment_change_status__change_comment_status',
		'discussion'
	);
	
}
add_action('admin_init', 'comment_change_status__admin_init');


function comment_change_status__change_comment_status() {
	
	echo '<fieldset>';
		
		echo '<legend class="hidden">'.__('Change Comment Status', 'comment-whitelist').'</legend>';
		
		echo ' <input name="change-comment-status-mail" type="text" id="change-comment-status-mail" value="' . attribute_escape(get_option('change-comment-status-mail')) . '" class="regular-text" />';
		echo '<br />';
		echo '<label for="change-comment-status-mail">'.__('Send messages to this e-mails', 'comment-change-status').'</label>';
		
	echo '</fieldset>';
	
}


require_once('comment-change-status-mail.php');

?>