<?php
/**
 * Security, checks if WordPress is running
 **/
if ( !function_exists( 'add_action' ) ) :
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
endif;



/**
 * Expire Admin class
 *
 * @package Expire
 * @author
 **/
final class Expire
{



	/**
	 * Plugin version number
	 *
	 * @var string
	 **/
	const version = '0.1.0';



	/**
	 * Hooks
	 *
	 * @access public
	 * @return void
	 * @author Ralf Hortt <me@horttcore.de>
	 **/
	public function __construct()
	{

		add_action( 'plugins_loaded', 'Expire::load_plugin_textdomain' );
		add_action( 'plugins_loaded', 'Expire::add_post_type_support' );
		add_action( 'pre_get_posts', 'Expire::pre_get_posts' );

	} // END __construct



	/**
	 * Add post type support
	 *
	 * @static
	 * @access public
	 * @return void
	 * @author Ralf Hortt <me@horttcore.de>
	 **/
	static public function add_post_type_support()
	{

		$post_types = get_post_types( array(
			'public' => TRUE,
		) );

		if ( !$post_types )
			return;

		foreach ( $post_types as $post_type ) :

			add_post_type_support( $post_type, 'expire' );

		endforeach;

	} // END add_post_type_support



	/**
	 * Load plugin translation
	 *
	 * @static
	 * @access public
	 * @return void
	 * @author Ralf Hortt <me@horttcore.de>
	 * @since v1.0.0
	 **/
	static public function load_plugin_textdomain()
	{

		load_plugin_textdomain( 'expire', false, dirname( plugin_basename( __FILE__ ) ) . '/../languages/'  );

	} // END load_plugin_textdomain



	/**
	 * Remove expired posts from query
	 *
	 * @static
	 * @access public
	 * @return void
	 * @author Ralf Hortt <me@horttcore.de>
	 **/
	static public function pre_get_posts( $query )
	{

		if ( is_admin() )
			return;

		$query->set( 'meta_query',
			array(
				'relation' => 'OR',
				array(
					'key' => '_expiration-date',
					'value' => time(),
					'compare' => '>=',
					'type' => 'NUMERIC',
				),
				array(
					'key' => '_expiration-date',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key' => '_expiration-date',
					'compare' => '=',
					'value' => '',
				)
			)
		);

	} // END pre_get_posts



} // END final class Expire

new Expire;
