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
final class Expire_Admin
{


	/**
	 * Hooks
	 *
	 * @access public
	 * @return void
	 * @author Ralf Hortt <me@horttcore.de>
	 **/
	public function __construct()
	{

		add_action( 'admin_enqueue_scripts', 'Expire_Admin::register_assets' );
		add_action( 'admin_print_scripts-post.php', 'Expire_Admin::enqueue_script' );	# Enqueue scripts
		add_action( 'admin_print_styles-post.php', 'Expire_Admin::enqueue_style' );		# Enqueue styles
		add_action( 'admin_head', 'Expire_Admin::add_admin_colums' );
		add_action( 'pre_get_posts', 'Expire_Admin::pre_get_posts' );
		add_action( 'post_submitbox_misc_actions', 'Expire_Admin::add_expiring_field' );
		add_action( 'save_post', 'Expire_Admin::save_post', 10, 2 );

	} // END __construct



	/**
	 * Add expire column
	 *
	 * @static
	 * @access public
	 * @param array $columns Columns
	 * @return array Columns
	 * @author Ralf Hortt <me@horttcore.de>
	 **/
	static public function add_column( $columns )
	{

		$columns['expire'] = __( 'Expiration date', 'expire' );

		return $columns;

	} // END add_column



	/**
	 * Add expiration date field
	 *
	 * @static
	 * @return void
	 * @author Ralf Hortt <me@horttcore.de>
	 **/
	static public function add_expiring_field()
	{

		$screen = get_current_screen();
		if ( !post_type_supports( $screen->post_type, 'expire' ) )
			return;

		global $post;

		$label = ( get_expiration_date( $post->ID ) ) ? get_expiration_date( $post->ID, apply_filters( 'expire-date-format', __( 'm.d.Y - H:i', 'expire' ) ) ) : __( 'Never' );
		$datetime = get_expiration_date( $post->ID, apply_filters( 'expire-date-format', __( 'm.d.Y - H:i', 'expire' ) ) );

		?>

		<div class="misc-pub-section curtime misc-pub-curtime">

			<span id="timestamp"><?php _e( 'Expiring:', 'expire' ); ?></span>
			<span class="setexpiringdate">
				<?php echo $label; ?>
			</span>

			<a href="#edit_expiringdate" class="edit-expiringdate hide-if-no-js">
				<span aria-hidden="true"><?php _e( 'Edit' ); ?></span>
				<span class="screen-reader-text"><?php _e( 'Edit expiring date', 'expire' ); ?></span>
			</a>

			<div id="expiringdatediv" class="hide-if-js">

				<div class="wrap">
					<input type="text" value="<?php echo $datetime ?>" name="expiration-date" id="expiration-date" />
					<a class="set-expiringdate hide-if-no-js button" href="#edit_expiringdate"><?php _e( 'OK' ); ?></a>
				</div>

				<div>
					<a class="cancel-expiringdate hide-if-no-js button-cancel" href="#edit_expiringdate"><?php _e( 'Cancel' ); ?></a>
				</div>

			</div>

		</div>

		<?php

		wp_nonce_field( 'save-expiration-date', 'expiration-date-nonce' );

	} // END add_expiring_field



	/**
	 * Add column hooks
	 *
	 * @static
	 * @access public
	 * @return void
	 * @author Ralf Hortt <me@horttcore.de>
	 **/
	static public function add_admin_colums()
	{

		$post_types = get_post_types();

		if ( !$post_types )
			return;

		foreach ( $post_types as $post_type ) :

			if ( !post_type_supports( $post_type, 'expire' ) )
				continue;

			switch ( $post_type ) :

				case 'post' :
					add_action( 'manage_posts_custom_column', 'Expire_Admin::render_column', 10, 2 );
					add_filter( 'manage_posts_columns', 'Expire_Admin::add_column' );
					add_filter( 'manage_edit-posts_sortable_columns', 'Expire_Admin::sortable_columns' );
					break;

				case 'page' :
					add_action( 'manage_pages_custom_column', 'Expire_Admin::render_column', 10, 2 );
					add_filter( 'manage_pages_columns', 'Expire_Admin::add_column' );
					add_filter( 'manage_edit-page_sortable_columns', 'Expire_Admin::sortable_columns' );
					break;

				default :
					add_action( 'manage_' . $post_type . '_custom_column', 'Expire_Admin::render_column', 10, 2 );
					add_filter( 'manage_' . $post_type . '_posts_columns', 'Expire_Admin::add_column' );
					add_filter( 'manage_edit-' . $post_type . '_sortable_columns', 'Expire_Admin::sortable_columns' );
					break;

			endswitch;

		endforeach;

	} // END add_admin_columns



	/**
	 * Enqueue scripts
	 *
	 * @static
	 * @access public
	 * @return void
	 * @author Ralf Hortt <me@horttcore.de>
	 **/
	static public function enqueue_script()
	{

		$screen = get_current_screen();
		if ( !post_type_supports( $screen->post_type, 'expire' ) )
			return;

		$locale = get_locale();
		$language = explode( '_', $locale );
		$language = $language[0];

		wp_enqueue_script( 'expire' );
		wp_localize_script( 'expire', 'Expire', array(
			'pickerConf' => apply_filters( 'expire-picker-configuration', array(
				'timepicker' => apply_filters( 'expire-timepicker', TRUE ),
				'format' => apply_filters( 'expire-date-format', __( 'm.d.Y - H:i', 'expire' ) ),
				'lang' => apply_filters( 'expire-datepicker-language', $language ),
			)),
			'never' => __( 'Never' ),
		) );

	} // END enqueue_script



	/**
	 * Enqueue styles
	 *
	 * @static
	 * @access public
	 * @return void
	 * @author Ralf Hortt <me@horttcore.de>
	 **/
	static public function enqueue_style()
	{

		$screen = get_current_screen();
		if ( !post_type_supports( $screen->post_type, 'expire' ) )
			return;

		wp_enqueue_style( 'expire' );

	} // END enqueue_style



	/**
	 * Sort by expiration date
	 *
	 * @static
	 * @access public
	 * @param WP_Query $query Query object
	 * @return void
	 * @author Ralf Hortt <me@horttcore.de>
	 * @since 1.1.0
	 **/
	static public function pre_get_posts( $query )
	{

		if ( 'expire' != $query->get( 'orderby' ) )
			return;

		$query->set( 'meta_key', '_expiration-date' );
        $query->set( 'orderby', 'meta_value_num' );

	} // END pre_get_posts



	/**
	 * Register assets
	 *
	 * @static
	 * @access public
	 * @return void
	 * @author Ralf Hortt <me@horttcore.de>
	 **/
	static public function register_assets()
	{

		wp_register_script( 'jquery.datetimepicker', plugins_url( '../scripts/jquery.datetimepicker.js', __FILE__ ), array( 'jquery' ), Expire::version, TRUE );
		wp_register_style( 'jquery.datetimepicker', plugins_url( '../styles/jquery.datetimepicker.css', __FILE__ ), array(), Expire::version );

		wp_register_script( 'expire', plugins_url( '../scripts/scripts.admin.js', __FILE__ ), array( 'jquery', 'jquery.datetimepicker' ), Expire::version, TRUE );
		wp_register_style( 'expire', plugins_url( '../styles/styles.admin.css', __FILE__ ), array( 'jquery.datetimepicker' ), Expire::version );

	} // END register_assets



	/**
	 * Output expiration column
	 *
	 * @static
	 * @access public
	 * @param str $column Column name
	 * @return void
	 * @author Ralf Hortt <me@horttcore.de>
	 **/
	static public function render_column( $column, $post_id )
	{

		switch ( $column ) :

			case 'expire' :
				echo get_expiration_date( $post_id, apply_filters( 'expire-date-format', __( 'm.d.Y - H:i', 'expire' ) ) );
				break;

		endswitch;

	} // END render_column



	/**
	 * Save expiration date
	 *
	 * @static
	 * @access public
	 * @param int $post_id Post ID
	 * @param WP_Post $post Post object
	 * @return void
	 * @author Ralf Hortt <me@horttcore.de>
	 **/
	static function save_post( $post_id, $post )
	{

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return;

		if ( !isset( $_POST['expiration-date-nonce'] ) || !wp_verify_nonce( $_POST['expiration-date-nonce'], 'save-expiration-date' ) )
			return;

		if ( !current_user_can( 'edit_post', $post_id ) )
			return;

		if ( $_POST['expiration-date'] ) :
			$datetime = DateTime::createFromFormat( apply_filters( 'expire-date-format', __( 'm.d.Y - H:i', 'expire' ) ), $_POST['expiration-date'] );
			$timestamp = ( $datetime ) ? $datetime->getTimestamp() : NULL;
		else :
			$timestamp = NULL;
		endif;

		update_post_meta( $post_id, '_expiration-date', $timestamp );

	} // END save_post



	/**
	 * Sortable columns
	 *
	 * @static
	 * @access public
	 * @param array $columns Sortable columns
	 * @return array Sortable columns
	 * @author Ralf Hortt <me@horttcore.de>
	 * @since 1.1.0
	 **/
	static public function sortable_columns( $columns )
	{

		$columns['expire'] = 'expire';

		return $columns;

	} // END sortable_columns


} // END final class Expire_Admin

new Expire_Admin;
