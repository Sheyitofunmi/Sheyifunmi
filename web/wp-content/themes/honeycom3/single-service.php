<?php
/**
 * Single service
 *
 * @package honeycom3
 */

require_once get_template_directory() . '/classes/class-service.php';

if ( ! class_exists( 'Timber\Post' ) ) {
	die( 'Timber\Post cannot be found. Please ensure fb-wp-hc3-theme-settings is installed and activated' );
}

$fb_services = ( isset( $fb_services ) && is_object( $fb_services ) ? $fb_services : new Honeycom3_Service() );

$context = Timber::context();

if ( post_password_required( $post->ID ) ) {
	Timber::render( '@wp/password-form-page.twig', $context );
} else {
	Timber::render( '@wp/services/single.twig', $context, TIMBER_CACHE_TIME );
}
