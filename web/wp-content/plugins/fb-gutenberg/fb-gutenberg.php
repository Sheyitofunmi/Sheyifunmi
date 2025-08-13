<?php
/**
 * Plugin Name: FB Gutenberg
 * Plugin URI: http://fatbeehive.com
 * Description: Class for Gutenberg customisations
 * Author: Fat Beehive
 * Author URI: http://fatbeehive.com
 * Version: 1.0
 *
 * @package FB Gutenberg
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once plugin_dir_path( __FILE__ ) . 'classes/class-fb-gutenberg.php';
require_once plugin_dir_path( __FILE__ ) . 'classes/class-fb-gutenberg-blocks.php';
require_once plugin_dir_path( __FILE__ ) . 'classes/class-fb-gutenberg-twiggify.php';
require_once plugin_dir_path( __FILE__ ) . 'classes/class-fb-gutenberg-twiggify-featured-promos.php';

$fb_gutenberg        = ( isset( $fb_gutenberg ) && is_object( $fb_gutenberg ) ? $fb_gutenberg : new FB_Gutenberg() );
$fb_gutenberg_blocks = ( isset( $fb_gutenberg_blocks ) && is_object( $fb_gutenberg_blocks ) ? $fb_gutenberg_blocks : new FB_Gutenberg_Blocks() );

/**
 * Block render callback.
 *
 * @param array  $block      The block settings and attributes.
 * @param string $content    The block content.
 * @param bool   $is_preview True during AJAX preview.
 */
function fb_gutenberg_render_callback( $block, $content = '', $is_preview = false ) {
	$context               = Timber::context();
	$context['block']      = $block;
	$context['is_preview'] = $is_preview;
	$context['post']       = Timber::get_post();

	if (!isset($context['post']) || !$context['post']) {
		$context['post'] = get_post(); // Get the current post
	}

	$twiggify_helper            = new FB_Gutenberg_Twiggify();
	if ($context['post']) {
		$context['fields'] = $twiggify_helper->add_alignment_classes_to_fields(
			get_fields(),
			$context['post']->ID
		);
	} else {
		$context['fields'] = []; // Avoid errors when no post context
	}
	$twiggify_data              = $twiggify_helper->twiggify_block_data( $block, $context['fields'], $context['post'] );
	$context['twiggified_data'] = apply_filters( 'fb_gutenberg_twiggify', $twiggify_data, $context );

	$slug = str_replace( 'acf/', '', $block['name'] );
	Timber::render( get_stylesheet_directory() . '/templates/blocks/' . $slug . '.twig', $context, false );
}
