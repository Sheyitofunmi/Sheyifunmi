<?php
/**
 * Page builder template
 *
 * This is for the page post-type
 *
 * @package honeycom3
 */

if ( ! class_exists( 'Timber\Post' ) ) {
	die( 'Timber\Post cannot be found. Please fb-wp-hc3-theme-settings is installed and activated' );
}

$context = Timber::context();

if ( post_password_required( $post->ID ) ) {
	Timber::render( '@wp/password-form-page.twig', $context );
} else {
	Timber::render( '@wp/page-builder.twig', $context, TIMBER_CACHE_TIME );
}
