<?php
/**
 * Single update
 *
 * @package honeycom3
 */
require_once get_template_directory() . '/classes/class-honeycom3-updates.php';

if ( ! class_exists( 'Timber\Post' ) ) {
	die( 'Timber\Post cannot be found. Please fb-wp-hc3-theme-settings is installed and activated' );
}

$fb_updates = ( isset( $fb_updates ) && is_object( $fb_updates ) ? $fb_updates : new Honeycom3_Updates() );

$context = Timber::context();

if ( post_password_required( $post->ID ) ) {
	Timber::render( '@wp/password-form-page.twig', $context );
} else {
	Timber::render( '@wp/news/single.twig', $context, TIMBER_CACHE_TIME );
}
