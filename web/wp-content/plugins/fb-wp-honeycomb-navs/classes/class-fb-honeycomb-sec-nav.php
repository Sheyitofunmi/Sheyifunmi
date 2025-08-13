<?php
/**
 * Plugin Name: FB Honeycomb Navs
 * Plugin URI: http://fatbeehive.com
 * Description: Class for creating a secondary nav array for HC2
 * Author: Fat Beehive
 * Author URI: http://fatbeehive.com
 * Version: 1.0
 *
 * @package FB Honeycomb Navs
 */

/**
 * Class FBHoneycombSecNav
 */
class FBHoneycombSecNav extends FBHoneycombGlobalNav {
	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->sec_nav = array();
		add_theme_support( 'menus' );
		add_action( 'init', array( $this, 'register_sec_nav_menu' ) );
		if ( ! is_admin() ) {
			add_action( 'init', array( $this, 'get_nav_data' ) );
			add_filter( 'timber/context', array( $this, 'add_sec_nav_to_timber' ) );
		}
	}

	/**
	 * Register a WP menu for the global navigation.
	 */
	public function register_sec_nav_menu() {
		register_nav_menu( 'secondary_navigation', __( 'Secondary Navigation', 'fb-hc2' ) );
	}

	/**
	 * Load the full tree items for the menu.
	 */
	protected function load_menu_tree() {
		$menu_locations = get_nav_menu_locations();
		if ( $menu_locations && isset( $menu_locations['secondary_navigation'] ) ) {
			$menu                  = wp_get_nav_menu_object( 'secondary_navigation' );
			$menu_items            = wp_get_nav_menu_items( $menu_locations['secondary_navigation'] );
			if ( $menu_items ) {
				$this->full_tree_items = $this->build_tree( $menu_items );
			}
		}
	}

	/**
	 * Add the page nav data to timber's context.
	 *
	 * @param array $context the timber context array.
	 * @return array $context the timber context array with nav data.
	 */
	public function add_sec_nav_to_timber( $context ) {
		$context['sec_nav'] = $this->formatted_nav;
		return $context;
	}
}
