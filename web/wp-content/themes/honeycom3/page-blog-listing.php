<?php
/**
 * Template Name: Blog listing
 *
 * @package honeycom3
 */

if ( ! class_exists( 'Timber\Post' ) ) {
	die( 'Timber\Post cannot be found. Please ensure fb-wp-hc3-theme-settings is installed and activated' );
}

$context = Timber::context();

// Get blog posts.
$paged = get_query_var( 'paged', 1 );
$args  = array(
	'post_type'      => 'post',
	'posts_per_page' => 10,
	'post_status'    => 'publish',
	'paged'          => $paged,
);

$blog_query = new WP_Query( $args );
$context['blog_posts'] = array();

if ( $blog_query->have_posts() ) {
	foreach ( $blog_query->posts as $blog_post ) {
		$categories = get_the_category( $blog_post->ID );
		$cat_names  = array();
		foreach ( $categories as $cat ) {
			$cat_names[] = $cat->name;
		}

		$context['blog_posts'][] = array(
			'_id'          => $blog_post->ID,
			'path'         => esc_url( get_the_permalink( $blog_post ) ),
			'title'        => esc_html( get_the_title( $blog_post ) ),
			'summary'      => wp_trim_words( wp_strip_all_tags( $blog_post->post_content ), 30, '...' ),
			'date'         => get_the_date( 'F j, Y', $blog_post ),
			'reading_time' => get_field( 'reading_time', $blog_post->ID ) ?: '5',
			'categories'   => $cat_names,
		);
	}
	$context['pagination'] = new Timber\Pagination( array(), $blog_query );
}

if ( post_password_required( $post->ID ) ) {
	Timber::render( '@wp/password-form-page.twig', $context );
} else {
	Timber::render( array( '@wp/blog/listing.twig', '@wp/news/listing.twig' ), $context, TIMBER_CACHE_TIME );
}
