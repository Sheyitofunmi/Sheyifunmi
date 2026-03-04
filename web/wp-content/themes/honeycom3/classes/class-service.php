<?php
/**
 * Service CPT
 *
 * @package honeycom3
 */

require_once get_template_directory() . '/classes/class-honeycom3-custom-post-type.php';

class Honeycom3_Service extends Honeycom3_Custom_Post_Type {

	public function __construct() {
		if ( is_singular( 'service' ) ) {
			add_filter( 'timber/context', array( $this, 'add_service_single_data_to_context' ) );
		} elseif ( is_page_template( 'page-services-listing.php' ) ) {
			add_filter( 'timber/context', array( $this, 'add_service_listing_data_to_context' ) );
			add_filter( 'timber/context', array( $this, 'add_services_feeds_to_context' ) );
		}
	}

	/**
	 * Add single service data to context.
	 */
	public function add_service_single_data_to_context( $context ) {
		$post_id = get_the_ID();

		$context['service'] = array(
			'service_description' => get_field( 'service_description', $post_id ),
			'cta_text'            => esc_html( get_field( 'cta_text', $post_id ) ),
			'cta_link'            => esc_url( get_field( 'cta_link', $post_id ) ),
			'display_order'       => intval( get_field( 'display_order', $post_id ) ),
		);

		// Icon.
		$icon = get_field( 'icon', $post_id );
		if ( $icon ) {
			$context['service']['icon'] = $icon;
		}

		// Features (repeater).
		$features = get_field( 'features', $post_id );
		if ( $features ) {
			$context['service']['features'] = $features;
		}

		// Service category (taxonomy).
		$service_categories = get_the_terms( $post_id, 'service_category' );
		if ( $service_categories && ! is_wp_error( $service_categories ) ) {
			$context['service']['categories'] = $service_categories;
		}

		// Sidebar.
		$sidebar = array();
		if ( $service_categories && ! is_wp_error( $service_categories ) ) {
			$cats = array();
			foreach ( $service_categories as $cat ) {
				$cats[] = array(
					'title' => esc_html( $cat->name ),
					'link'  => $this->get_term_filtered_url( $cat ),
				);
			}
			$sidebar['cats'] = $cats;
		}
		$context['service_sidebar'] = $sidebar;

		return $context;
	}

	/**
	 * Add listing page data to context.
	 */
	public function add_service_listing_data_to_context( $context ) {
		$hero_metas = array();
		$context['hero_metas'] = $hero_metas;
		return $context;
	}

	/**
	 * Add service feeds to context.
	 */
	public function add_services_feeds_to_context( $context ) {
		$posts = $this->get_services();

		$context['services_feed'] = isset( $posts['posts'] ) ? $posts['posts'] : false;
		$context['pagination']    = isset( $posts['pagination'] ) ? $posts['pagination'] : false;

		return $context;
	}

	/**
	 * Get all services ordered by display_order.
	 */
	private function get_services() {
		$paged = get_query_var( 'paged', 1 );
		$args  = array(
			'post_type'      => 'service',
			'posts_per_page' => -1,
			'meta_key'       => 'display_order',
			'orderby'        => 'meta_value_num',
			'order'          => 'ASC',
			'paged'          => $paged,
		);

		$results = new WP_Query( $args );
		if ( is_array( $results->posts ) && count( $results->posts ) > 0 ) {
			return array(
				'posts'      => $this->twiggify_service_posts( $results->posts ),
				'pagination' => new Timber\Pagination( array(), $results ),
			);
		} else {
			return array();
		}
	}

	/**
	 * Transform WP posts into Twig-ready arrays.
	 */
	private function twiggify_service_posts( $service_posts ) {
		$cards = array();
		if ( ! empty( $service_posts ) ) {
			foreach ( $service_posts as $key => $post ) {
				$cards[ $key ]['_id']         = $post->ID;
				$cards[ $key ]['path']        = esc_url( get_the_permalink( $post ) );
				$cards[ $key ]['title']       = esc_html( get_the_title( $post ) );
				$cards[ $key ]['summary']     = nl2br( wp_strip_all_tags( get_field( 'service_description', $post->ID ) ) );

				// Icon.
				$icon = get_field( 'icon', $post->ID );
				if ( $icon ) {
					$cards[ $key ]['image_data'] = array(
						'src' => esc_url( $icon['url'] ),
						'alt' => esc_attr( $icon['alt'] ),
					);
				}

				// Features.
				$features = get_field( 'features', $post->ID );
				if ( $features ) {
					$cards[ $key ]['features'] = $features;
				}

				// Service category label.
				$service_categories = get_the_terms( $post->ID, 'service_category' );
				if ( $service_categories && ! is_wp_error( $service_categories ) ) {
					$cards[ $key ]['label']      = esc_html( $service_categories[0]->name );
					$cards[ $key ]['label_path'] = $this->get_term_filtered_url( $service_categories[0] );
				}

				// CTA.
				$cta_text = get_field( 'cta_text', $post->ID );
				$cta_link = get_field( 'cta_link', $post->ID );
				if ( $cta_text && $cta_link ) {
					$cards[ $key ]['cta'] = array(
						'text' => esc_html( $cta_text ),
						'link' => esc_url( $cta_link ),
					);
				}
			}
		}
		return $cards;
	}

	/**
	 * Build a filtered URL for a taxonomy term.
	 */
	private function get_term_filtered_url( $term ) {
		$base_url = get_the_permalink( SERVICES_LISTING_PAGE );
		$term_filtered_url = $base_url . '?' . $term->taxonomy . '=' . $term->term_id . '&search=1';
		return $term_filtered_url;
	}
}
