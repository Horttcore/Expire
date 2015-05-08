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
	const version = '0.1.1';



	/**
	 * Hooks
	 *
	 * @access public
	 * @return void
	 * @author Ralf Hortt <me@horttcore.de>
	 **/
	public function __construct()
	{

		#add_filter( 'cron_schedules', 'Expire::cron_schedules' );
		#add_action( 'expire', 'Expire::expire_posts' );
		add_action( 'plugins_loaded', 'Expire::load_plugin_textdomain' );
		add_action( 'plugins_loaded', 'Expire::add_post_type_support' );
		add_action( 'pre_get_posts', 'Expire::pre_get_posts', 9999 );
		#add_action( 'wp', 'Expire::add_cron' );

	} // END __construct



	/**
	 * Add cron
	 *
	 * @static
	 * @access public
	 * @return void
	 * @since 1.1.0
	 * @author Ralf Hortt <me@horttcore.de>
	 **/
	static public function add_cron()
	{

		if ( !wp_next_scheduled( 'expire-posts' ) )
			wp_schedule_event( time(), 'every-minute', 'expire' );

	} // END add_cron



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
	 * Custom cron schedules
	 *
	 * @static
	 * @access public
	 * @param array $schedules Cron schedules
	 * @return array Cron schedules
	 * @since 1.1.0
	 * @author Ralf Hortt <me@horttcore.de>
	 **/
	static public function cron_schedules( $schedules )
	{

		$schedules['every-minute'] = array(
	 		'interval' => 60,
	 		'display' => __( 'Every Minute', 'expire' )
	 	);

		return $schedules;

	} // END cron_schedules



	/**
	 * Expire posts
	 *
	 * @static
	 * @access public
	 * @return void
	 * @since 1.1.0
	 * @author Ralf Hortt <me@horttcore.de>
	 **/
	static public function expire_posts()
	{

		$query = new WP_Query( array(
			'post_type' => 'any',
			'post_status' => 'publish',
			'meta_query' => array(
				array(
					'key' => '_expiration-date',
					'value' => time(),
					'compare' => '<=',
					'type' => 'NUMERIC',
				),
			)
		) );

		if ( !$query->have_posts() )
			return;

		while ( $query->have_posts() ) : $query->the_post();

			wp_update_post( array(
				'ID' => get_the_ID(),
				'post_status' => 'draft',
			) );

		endwhile;

		wp_reset_query();

	} // END expire_posts



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

		if ( is_admin() || !$query->is_main_query() )
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
