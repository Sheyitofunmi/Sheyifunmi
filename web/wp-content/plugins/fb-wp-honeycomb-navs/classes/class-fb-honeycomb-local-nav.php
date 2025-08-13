<?php
/**
 * Plugin Name: FB Honeycomb Navs
 * Plugin URI: http://fatbeehive.com
 * Description: Class for creating a local nav array for HC2
 * Author: Fat Beehive
 * Author URI: http://fatbeehive.com
 * Version: 1.0
 *
 * @package FB Honeycomb Navs
 */

/**
 * Class FBHoneycombLocalNav
 */
class FBHoneycombLocalNav {
	/**
	 * Array to store the nav data.
	 *
	 * @var array
	 */
	private $local_nav;

	/**
	 * Array to store the list of excluded pages.
	 *
	 * @var array
	 */
	protected $excluded_pages = array();

	/**
	 * Array of listing pages.
	 *
	 * @var array
	 */
	protected $listing_pages = array();

	/**
	 * Array of CPTs.
	 *
	 * @var array
	 */
	protected $cpt_names = array();

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->local_nav = array();
		add_action( 'wp', array( $this, 'get_nav_data' ) );
		add_filter( 'timber/context', array( $this, 'add_local_nav_to_timber' ) );
		add_filter( 'timber/context', array( $this, 'maybe_add_cpt_single_breadcrumbs_to_timber' ) );
	}

	/**
	 * Gets the nav data for top level pages.
	 */
	public function get_nav_data() {
		if ( is_admin() || is_search() || is_front_page() ) {
			return;
		}

		global $post;
		if ( ! $post ) {
			// e.g. 404 page.
			return false;
		}
		$post_id   = $post->ID;
		$post_type = $post->post_type;
		// Bail - Do not load the local nav for these pages.
		$this->listing_pages = apply_filters( 'fb_nav_listing_pages_no_local_nav', $this->listing_pages );
		$this->cpt_names     = apply_filters( 'fb_nav_cpt_no_local_nav', $this->cpt_names );

		if ( in_array( $post_id, $this->listing_pages ) || in_array( $post_type, $this->cpt_names ) ) {
			return false;
		}

		// No pages to exclude from nav structure.
		// Override   $this->excluded_pages on a child class if needed.
		if ( empty( $this->excluded_pages ) ) {
			$this->excluded_pages = array();
		}

		$args = array(
			'sort_column' => 'menu_order',
			'parent'      => 0,
			'exclude'     => $this->excluded_pages,
		);

		$pages = get_pages( $args );
		if ( $pages ) {
			foreach ( $pages as $page ) {
				$localised_page_id = $this->get_localised_post_id( $page->ID );
				$this->local_nav[] = $this->get_page_data( $localised_page_id );
			}
		}
	}

	/**
	 * Add the page nav data to timber's context.
	 *
	 * @param array $context the timber context array.
	 * @return array $context the timber context array with nav data.
	 */
	public function add_local_nav_to_timber( $context ) {
		$context['subnav'] = $this->local_nav;
		return $context;
	}

	/**
	 * Gets the nav data for top level pages.
	 *
	 * @param int $page_id the id of the page to retrieve data for.
	 * @return array $nav array of nav data for the page.
	 */
	protected function get_page_data( $page_id ) {
		$nav          = false;
		$is_published = get_post_status( $page_id ) === 'publish';
		if ( $page_id && $is_published ) {
			$the_title = apply_filters( 'fb_honeycomb_local_nav_override_title', get_the_title( $page_id ), $page_id );
			$nav = array(
				'text'         => sanitize_text_field( $the_title ),
				'url'          => get_the_permalink( $page_id ),
				'active'       => is_page( $page_id ),
				'active_trail' => $this->page_is_in_active_trail( $page_id ),
			);

			$args['orderby']     = 'menu_order';
			$args['order']       = 'ASC';
			$args['post_parent'] = $page_id;
			$args['post_type']   = 'page';
			$args['exclude']     = $this->excluded_pages;
			$children            = get_children( $args );
			if ( $children ) {
				$nav['submenu'] = array();
				foreach ( $children as $child ) {
					$page_data = $this->get_page_data( $child->ID );
					// Prevent not published pages to be added to the submenu.
					if ( $page_data ) {
						$nav['submenu'][] = $page_data;
					}
				}
			}
		}

		return $nav;
	}

	/**
	 * Checks if a page id is an ancestor of an active page.
	 *
	 * @param int $page_id the id of the page being checked.
	 */
	private function is_ancestor_of_active_page( $page_id ) {
		$post = get_post(); // Gets the current global post object.

		if ( ! is_object( $post ) ) {
			return false;
		}
		$ancestors = $post->ancestors;

		return is_array( $ancestors ) && in_array( $page_id, $ancestors );
	}

	/**
	 * Checks if a page id should be marked as being in the active trail.
	 *
	 * @param int $page_id the id of the page being checked.
	 */
	private function page_is_in_active_trail( $page_id ) {
		if ( $this->is_ancestor_of_active_page( $page_id ) || is_page( $page_id ) ) {
			return true;
		} else {
			return false;
		}
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
	 * Add the breadcrumbs data to timber's context if a CPT detail page.
	 *
	 * @param array $context the timber context array.
	 * @return array $context the timber context array with breadcrumbs data.
	 */
	public function maybe_add_cpt_single_breadcrumbs_to_timber( $context ) {
		global $post;

		$breadcrumbs_nav = array();

		if ( is_single() ) {
			$post_type_object = get_post_type_object( get_post_type() );

			if ( isset( $post_type_object->labels->name ) ) {
				$post_type_object_plural_label = $post_type_object->labels->name;
			}

			$constant_name = strtoupper( $post_type_object_plural_label . '_LISTING_PAGE' );
			$constant_name = str_replace( ' ', '_', $constant_name );
			if ( defined( $constant_name ) ) {
				$listing_page_id = constant( $constant_name );
			}

			if ( ! $listing_page_id ) {
				return $context;
			}

			$ancestors = array_reverse( get_ancestors( $listing_page_id, 'page' ) );
			if ( ! empty( $ancestors ) ) {
				foreach ( $ancestors as $key => $ancestor ) {
					$breadcrumbs_nav[ $key ]['text']         = get_the_title( $ancestor );
					$breadcrumbs_nav[ $key ]['url']          = get_the_permalink( $ancestor );
					$breadcrumbs_nav[ $key ]['active']       = false;
					$breadcrumbs_nav[ $key ]['active_trail'] = true;
				}
			}

			$key = count( $breadcrumbs_nav );

			// Listing page.
			$breadcrumbs_nav[ $key ]['text']         = get_the_title( $listing_page_id );
			$breadcrumbs_nav[ $key ]['url']          = get_the_permalink( $listing_page_id );
			$breadcrumbs_nav[ $key ]['active']       = false;
			$breadcrumbs_nav[ $key ]['active_trail'] = true;

			$key = count( $breadcrumbs_nav );

			// CPT Detail page.
			$breadcrumbs_nav[ $key ]['text']         = get_the_title( $post->id );
			$breadcrumbs_nav[ $key ]['url']          = get_the_permalink( $post->id );
			$breadcrumbs_nav[ $key ]['active']       = true;
			$breadcrumbs_nav[ $key ]['active_trail'] = true;

			$context['cpt_breadcrumbs'] = $breadcrumbs_nav;
		}
		return $context;
	}
}
