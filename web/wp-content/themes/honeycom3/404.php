<?php
/**
 * 404 Page
 *
 * @package honeycom3
 */

if ( ! class_exists( 'Timber\Post' ) ) {
	die( 'Timber\Post cannot be found. Please fb-wp-hc3-theme-settings is installed and activated' );
}

$context = Timber::context();

Timber::render( '@wp/404.twig', $context, TIMBER_CACHE_TIME );
