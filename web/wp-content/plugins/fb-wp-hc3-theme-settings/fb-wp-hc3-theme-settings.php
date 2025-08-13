<?php
/**
 * Plugin Name: FB WP HC3 Theme Settings
 * Plugin URI: http://fatbeehive.com
 * Description: Class for theme settings and customisations
 * Author: Fat Beehive
 * Author URI: http://fatbeehive.com
 * Version: 1.0
 *
 * @package FB WP HC3 Theme Settings
 */

/**
 * Recommended WP Plugin security in case server is misconfigured.
 *
 * @see https://codex.wordpress.org/Writing_a_Plugin
 */
defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

// Load Composer dependencies.
require_once __DIR__ . '/vendor/autoload.php';
Timber\Timber::init(); // Initialise timber.

require_once plugin_dir_path( __FILE__ ) . 'classes/class-fb-wp-hc3-theme-settings.php';
$fb_wp_hc3_theme_settings = ( isset( $fb_wp_hc3_theme_settings ) && is_object( $fb_wp_hc3_theme_settings ) ? $fb_wp_hc3_theme_settings : new FB_WP_HC3_Theme_Settings() );
