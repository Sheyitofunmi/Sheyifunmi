<?php
/**
 * Single library
 *
 * @package honeycom3
 */
require_once get_template_directory() . '/classes/class-honeycom3-library.php';

if ( ! class_exists( 'Timber\Post' ) ) {
	die( 'Timber\Post cannot be found. Please fb-wp-hc3-theme-settings is installed and activated' );
}

$fb_library = ( isset( $fb_library ) && is_object( $fb_library ) ? $fb_library : new Honeycom3_Library() );

$context = Timber::context();

if ( post_password_required( $post->ID ) ) {
	Timber::render( '@wp/password-form-page.twig', $context );
} else {
	Timber::render( '@wp/resources/single.twig', $context, TIMBER_CACHE_TIME );
}
