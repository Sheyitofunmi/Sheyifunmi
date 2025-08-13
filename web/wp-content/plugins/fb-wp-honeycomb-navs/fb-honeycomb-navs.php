<?php
/**
 * Plugin Name: FB Honeycomb Navs
 * Plugin URI: http://fatbeehive.com
 * Description: Class to create navigation arrays for HC2
 * Author: Fat Beehive
 * Author URI: http://fatbeehive.com
 * Version: 1.0
 *
 * @package FB Honeycomb Navs
 */

/**
 * Recommended WP Plugin security in case server is misconfigured.
 *
 * @see https://codex.wordpress.org/Writing_a_Plugin
 */
defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

require_once plugin_dir_path( __FILE__ ) . 'classes/class-fb-honeycomb-local-nav.php';
require_once plugin_dir_path( __FILE__ ) . 'classes/class-fb-honeycomb-global-nav.php';
require_once plugin_dir_path( __FILE__ ) . 'classes/class-fb-honeycomb-sec-nav.php';
$fb_hc_local_nav  = ( isset( $fb_hc_local_nav ) && is_object( $fb_hc_local_nav ) ? $fb_hc_local_nav : new FBHoneycombLocalNav() );
$fb_hc_global_nav = ( isset( $fb_hc_global_nav ) && is_object( $fb_hc_global_nav ) ? $fb_hc_global_nav : new FBHoneycombGlobalNav() );
$fb_hc_sec_nav    = ( isset( $fb_hc_sec_nav ) && is_object( $fb_hc_sec_nav ) ? $fb_hc_sec_nav : new FBHoneycombSecNav() );
