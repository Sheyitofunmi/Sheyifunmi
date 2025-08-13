<?php
/**
 * Plugin Name: FB Honeycomb Navs
 * Plugin URI: http://fatbeehive.com
 * Description: Class for creating a global nav array for HC2
 * Author: Fat Beehive
 * Author URI: http://fatbeehive.com
 * Version: 1.0
 *
 * @package FB Honeycomb Navs
 */

/**
 * Class FBHoneycombGlobalNav
 */
class FBHoneycombGlobalNav {
	/**
	 * Array to store the nav tree.
	 *
	 * @var array
	 */
	protected $full_tree_items;

	/**
	 * Array to store the formatted nav data.
	 *
	 * @var array
	 */
	protected $formatted_nav;

	/**
	 * Array to store the list of excluded pages.
	 *
	 * @var array
	 */
	protected $excluded_pages;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->formatted_nav = array();
		add_theme_support( 'menus' );
		add_action( 'init', array( $this, 'register_global_nav_menu' ) );
		add_action( 'wp_trash_post', '_wp_delete_post_menu_item' );
		if ( ! is_admin() ) {
			add_action( 'init', array( $this, 'get_nav_data' ) );
			add_filter( 'timber/context', array( $this, 'add_global_nav_to_timber' ) );
		}
	}

	/**
	 * Register a WP menu for the global navigation.
	 */
	public function register_global_nav_menu() {
		register_nav_menu( 'global_navigation', __( 'Global Navigation', 'fb-hc2' ) );
	}

	/**
	 * Load the full tree items for the menu.
	 */
	protected function load_menu_tree() {
		$menu_locations = get_nav_menu_locations();
		if ( $menu_locations && isset( $menu_locations['global_navigation'] ) ) {
			$menu                  = wp_get_nav_menu_object( 'global_navigation' );
			$menu_items            = wp_get_nav_menu_items( $menu_locations['global_navigation'] );
			$this->full_tree_items = $this->build_tree( $menu_items );
		}
	}

	/**
	 * Convert the WP menu into a tree.
	 *
	 * @param array  $elements the menu items.
	 * @param string $parent_id the parent id, used on recursive call.
	 * @return array $branch the tree branch.
	 */
	protected function build_tree( array &$elements, $parent_id = 0 ) {
		$branch = array();
		foreach ( $elements as &$element ) {
			if ( (int) $element->menu_item_parent === $parent_id ) {
				$children = $this->build_tree( $elements, $element->ID );
				if ( $children ) {
					$element->children = $children;
				}
				$branch[ $element->ID ] = $element;
				unset( $element );
			}
		}
		return $branch;
	}

	/**
	 * Gets the nav data for top level pages.
	 */
	public function get_nav_data() {
		$this->load_menu_tree();
		$the_menu = array();
		if ( $this->full_tree_items ) {
			foreach ( $this->full_tree_items as $key => $menu_item ) {
				$menu_item_in_active_trail  = false;
				$the_menu[ $key ]['url']    = $menu_item->url;
				$the_menu[ $key ]['text']   = sanitize_text_field( $menu_item->title );
				$the_menu[ $key ]['target'] = $menu_item->target;

				$the_menu[ $key ]['active'] = $this->page_is_in_active_trail( $menu_item->object_id, $this->full_tree_items );
				if ( ! empty( $menu_item->children ) ) {
					foreach ( $menu_item->children as $child_key => $child ) {
						$the_menu[ $key ]['submenu'][ $child_key ]['url']    = $child->url;
						$the_menu[ $key ]['submenu'][ $child_key ]['text']   = sanitize_text_field( $child->title );
						$the_menu[ $key ]['submenu'][ $child_key ]['target'] = $child->target;
						$the_menu[ $key ]['submenu'][ $child_key ]['active'] = $this->page_is_in_active_trail( $child->object_id, $this->full_tree_items );
						if ( ! empty( $child->children ) ) {
							foreach ( $child->children as $grandchild_key => $grandchild ) {
								$the_menu[ $key ]['submenu'][ $child_key ]['submenu'][ $grandchild_key ]['url']    = $grandchild->url;
								$the_menu[ $key ]['submenu'][ $child_key ]['submenu'][ $grandchild_key ]['text']   = sanitize_text_field( $grandchild->title );
								$the_menu[ $key ]['submenu'][ $child_key ]['submenu'][ $grandchild_key ]['target']    = $grandchild->target;
								$the_menu[ $key ]['submenu'][ $child_key ]['submenu'][ $grandchild_key ]['active'] = $this->page_is_in_active_trail( $grandchild->object_id, $this->full_tree_items );
							}
							if ( $this->page_is_in_active_trail( $child->object_id, $this->full_tree_items ) || $this->page_is_in_active_trail( $grandchild->object_id, $this->full_tree_items ) ) {
								$menu_item_in_active_trail = true;
							}
						}
					}
					if ( $menu_item_in_active_trail ) {
						$the_menu[ $key ]['active'] = true;
					}
				}
			}
		}
		$this->formatted_nav = $the_menu;
	}


	/**
	 * Add the page nav data to timber's context.
	 *
	 * @param array $context the timber context array.
	 * @return array $context the timber context array with nav data.
	 */
	public function add_global_nav_to_timber( $context ) {
		$context['global_nav'] = $this->formatted_nav;
		return $context;
	}

	/**
	 * Gets the post id of the current page in the current language.
	 *
	 * @param int    $id the post id.
	 * @param string $object_type the object/post type.
	 */
	public function get_localised_post_id( $id, $object_type = 'page' ) {
		return apply_filters( 'wpml_object_id', $id, $object_type, true );
	}

	/**
	 * Checks if a page id is an ancestor of an active page.
	 *
	 * @param int   $page_id the id of the page being checked.
	 * @param array $tree the menu tree.
	 */
	private function is_ancestor_of_active_page( $page_id, $tree ) {
		$post = get_post(); // Gets the current global post object.

		if ( ! is_object( $post ) ) {
			return false;
		}
		$ancestors = $post->ancestors;

		$is_page_hierarchy_ancestor = is_array( $ancestors ) && in_array( $page_id, $ancestors );

		return $is_page_hierarchy_ancestor;
	}

	/**
	 * Checks if a page id should be marked as being in the active trail.
	 *
	 * @param int   $page_id the id of the page being checked.
	 * @param array $tree the menu tree.
	 */
	private function page_is_in_active_trail( $page_id, $tree ) {
		if ( $this->is_ancestor_of_active_page( $page_id, $tree ) || is_page( $page_id ) ) {
			return true;
		} else {
			return false;
		}
	}
}
