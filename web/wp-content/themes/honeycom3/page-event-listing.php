<?php
/**
 * Template Name: Events listing
 *
 * @package honeycom3
 */

require_once get_template_directory() . '/classes/class-honeycom3-events.php';

if ( ! class_exists( 'Timber\Post' ) ) {
	die( 'Timber\Post cannot be found. Please fb-wp-hc3-theme-settings is installed and activated' );
}

$fb_events = ( isset( $fb_events ) && is_object( $fb_events ) ? $fb_events : new Honeycom3_Events() );

$context = Timber::context();

if ( post_password_required( $post->ID ) ) {
	Timber::render( '@wp/password-form-page.twig', $context );
} else {
	Timber::render( '@wp/events/listing.twig', $context, TIMBER_CACHE_TIME );
}
