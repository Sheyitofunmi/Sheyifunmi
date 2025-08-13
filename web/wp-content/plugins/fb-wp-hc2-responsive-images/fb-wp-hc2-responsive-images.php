<?php
/**
 * Plugin Name: FB WP HC2 Responsive Images
 * Plugin URI: http://fatbeehive.com
 * Description: Class for preparing responsive images data
 * Author: Fat Beehive
 * Author URI: http://fatbeehive.com
 * Version: 1.0
 *
 * @package FB WP HC2 Responsive Images
 */

/**
 * Recommended WP Plugin security in case server is misconfigured.
 *
 * @see https://codex.wordpress.org/Writing_a_Plugin
 */
defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

require_once plugin_dir_path( __FILE__ ) . 'classes/class-fb-wp-hc2-responsive-images.php';
$fb_wp_hc2_responsive_images = ( isset( $fb_wp_hc2_responsive_images ) && is_object( $fb_wp_hc2_responsive_images ) ? $fb_wp_hc2_responsive_images : new FB_WP_HC2_Responsive_Images() );
