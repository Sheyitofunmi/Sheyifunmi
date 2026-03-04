<?php
/**
 * Single portfolio project
 *
 * @package honeycom3
 */

require_once get_template_directory() . '/classes/class-portfolio-project.php';

if ( ! class_exists( 'Timber\Post' ) ) {
	die( 'Timber\Post cannot be found. Please ensure fb-wp-hc3-theme-settings is installed and activated' );
}

$fb_projects = ( isset( $fb_projects ) && is_object( $fb_projects ) ? $fb_projects : new Honeycom3_Portfolio_Project() );

$context = Timber::context();

if ( post_password_required( $post->ID ) ) {
	Timber::render( '@wp/password-form-page.twig', $context );
} else {
	Timber::render( '@wp/projects/single.twig', $context, TIMBER_CACHE_TIME );
}
