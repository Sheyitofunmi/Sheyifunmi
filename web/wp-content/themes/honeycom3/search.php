<?php
/**
 * Search template
 *
 * @package honeycom3
 */

if ( ! class_exists( 'Timber\Post' ) ) {
	die( 'Timber\Post cannot be found. Please fb-wp-hc3-theme-settings is installed and activated' );
}

$context = Timber::context();

$search_results        = $fb_wp_hc3_theme_settings->get_search_results();
$context['feed']       = $search_results['posts'] ?? false;
$context['pagination'] = $search_results['pagination'] ?? false;

Timber::render( '@wp/search.twig', $context, TIMBER_CACHE_TIME );
