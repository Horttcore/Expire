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
 * undocumented function
 *
 * @return void
 * @author
 **/
function get_expiration_date( $post_id, $date_format = FALSE )
{

	$expire_date = get_post_meta( $post_id, '_expiration-date', TRUE );

	if ( !$expire_date )
		return;

	if ( FALSE === $date_format )
		return $expire_date;

	$date_format = ( TRUE !== $date_format ) ? $date_format : get_option( 'date_format' );

	return date_i18n( $date_format, $expire_date );

}



/**
 * undocumented function
 *
 * @return void
 * @author
 **/
function the_expiration_date( $date_format = FALSE )
{

	$date_format = ( FALSE !== $date_format ) ? $date_format : get_option( 'date_format' );
	echo get_expiration_date( get_the_ID(), $date_format );

}
