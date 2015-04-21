<?php
/**
 * Plugin Name: Expire
 * Plugin URI:
 * Text Domain: expire
 * Domain Path: /languages
 * Description: Expiration date for post types
 * Author: Ralf Hortt
 * Author URI: http://horttcore.de/
 * Version: 0.1.0
 */



/**
 * Security, checks if WordPress is running
 **/
if ( !function_exists( 'add_action' ) ) :
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
endif;

require( 'classes/class.expire.php');
require( 'includes/template-tags.php');

if ( is_admin() ) :
	require( 'classes/class.expire.admin.php');
endif;
