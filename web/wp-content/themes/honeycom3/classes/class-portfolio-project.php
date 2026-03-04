<?php
/**
 * Portfolio Project CPT
 *
 * @package honeycom3
 */

require_once get_template_directory() . '/classes/class-honeycom3-custom-post-type.php';

class Honeycom3_Portfolio_Project extends Honeycom3_Custom_Post_Type {

	public function __construct() {
		if ( is_singular( 'portfolio_project' ) ) {
			add_filter( 'timber/context', array( $this, 'add_project_single_data_to_context' ) );
		} elseif ( is_page_template( 'page-projects-listing.php' ) ) {
			add_filter( 'timber/context', array( $this, 'add_project_listing_data_to_context' ) );
			add_filter( 'timber/context', array( $this, 'add_project_listing_search_data_to_context' ) );
			add_filter( 'timber/context', array( $this, 'add_projects_feeds_to_context' ) );
		}
	}

	/**
	 * Add single project data to context.
	 */
	public function add_project_single_data_to_context( $context ) {
		$post_id = get_the_ID();

		// Project meta.
		$context['project'] = array(
			'client_name'  => esc_html( get_field( 'client_name', $post_id ) ),
			'project_url'  => esc_url( get_field( 'project_url', $post_id ) ),
			'challenge'    => get_field( 'challenge', $post_id ),
			'solution'     => get_field( 'solution', $post_id ),
			'my_role'      => esc_html( get_field( 'my_role', $post_id ) ),
			'project_date' => esc_html( get_field( 'project_date', $post_id ) ),
			'is_featured'  => get_field( 'is_featured', $post_id ),
		);

		// Key outcomes (repeater).
		$key_outcomes = get_field( 'key_outcomes', $post_id );
		if ( $key_outcomes ) {
			$context['project']['key_outcomes'] = $key_outcomes;
		}

		// Gallery.
		$gallery = get_field( 'gallery', $post_id );
		if ( $gallery ) {
			$context['project']['gallery'] = $gallery;
		}

		// Technologies (taxonomy).
		$technologies = get_the_terms( $post_id, 'technology' );
		if ( $technologies && ! is_wp_error( $technologies ) ) {
			$context['project']['technologies'] = $technologies;
		}

		// Project type (taxonomy).
		$project_types = get_the_terms( $post_id, 'project_type' );
		if ( $project_types && ! is_wp_error( $project_types ) ) {
			$context['project']['project_types'] = $project_types;
		}

		// Linked testimonial.
		$testimonial = get_field( 'linked_testimonial', $post_id );
		if ( $testimonial ) {
			$context['project']['testimonial'] = array(
				'client_name'  => esc_html( get_field( 'client_name', $testimonial->ID ) ),
				'client_role'  => esc_html( get_field( 'client_role', $testimonial->ID ) ),
				'quote'        => esc_html( get_field( 'quote', $testimonial->ID ) ),
				'client_photo' => get_field( 'client_photo', $testimonial->ID ),
				'rating'       => intval( get_field( 'rating', $testimonial->ID ) ),
			);
		}

		// Related projects (same technology terms).
		$context['related_projects'] = $this->get_related_projects( $post_id );

		// Sidebar data.
		$sidebar = array();

		if ( $technologies && ! is_wp_error( $technologies ) ) {
			$tags = array();
			foreach ( $technologies as $tech ) {
				$tags[] = array(
					'title' => esc_html( $tech->name ),
					'link'  => $this->get_term_filtered_url( $tech ),
				);
			}
			$sidebar['tags'] = $tags;
		}

		if ( $project_types && ! is_wp_error( $project_types ) ) {
			$cats = array();
			foreach ( $project_types as $type ) {
				$cats[] = array(
					'title' => esc_html( $type->name ),
					'link'  => $this->get_term_filtered_url( $type ),
				);
			}
			$sidebar['cats'] = $cats;
		}

		$context['project_sidebar'] = $sidebar;

		return $context;
	}

	/**
	 * Add project listing search/filter data to context.
	 */
	public function add_project_listing_search_data_to_context( $context ) {
		$context['search']['technologies']  = $this->get_taxonomy_terms( 'technology' );
		$context['search']['project_types'] = $this->get_taxonomy_terms( 'project_type' );

		if ( $this->is_search() ) {
			$technology                         = $this->get_query_string_value( 'technology' );
			$context['selected']['technology']  = $technology;
			$project_type                       = $this->get_query_string_value( 'project-type' );
			$context['selected']['project_type'] = $project_type;
		}

		return $context;
	}

	/**
	 * Add listing page data to context.
	 */
	public function add_project_listing_data_to_context( $context ) {
		$hero_metas      = array();
		$contact_details = get_field( 'contact_details_section' );

		if ( ! empty( $contact_details['email_address'] ) ) {
			$hero_metas['email']['title'] = 'Email: ';
			$hero_metas['email']['info']  = esc_html( $contact_details['email_address'] );
		}

		$context['hero_metas'] = $hero_metas;
		return $context;
	}

	/**
	 * Add project feeds (featured + listing) to context.
	 */
	public function add_projects_feeds_to_context( $context ) {
		$featured_posts     = $this->get_featured_projects();
		$featured_posts_ids = array_column( $featured_posts, '_id' );
		$posts              = $this->get_projects( $featured_posts_ids );

		$context['featured_cards']  = $featured_posts;
		$context['projects_feed']   = isset( $posts['posts'] ) ? $posts['posts'] : false;
		$context['pagination']      = isset( $posts['pagination'] ) ? $posts['pagination'] : false;

		return $context;
	}

	/**
	 * Get featured projects.
	 */
	private function get_featured_projects( $posts_per_page = 3 ) {
		if ( $this->is_search() ) {
			return array();
		}

		$args = array(
			'post_type'      => 'portfolio_project',
			'posts_per_page' => $posts_per_page,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'meta_query'     => array(
				array(
					'key'   => 'is_featured',
					'value' => 1,
				),
			),
		);

		$results = new WP_Query( $args );
		if ( is_array( $results->posts ) && count( $results->posts ) > 0 ) {
			return $this->twiggify_project_posts( $results->posts );
		} else {
			return array();
		}
	}

	/**
	 * Get projects with filtering.
	 */
	private function get_projects( $exclude_posts ) {
		$paged = get_query_var( 'paged', 1 );
		$args  = array(
			'post_type'    => 'portfolio_project',
			'orderby'      => 'date',
			'order'        => 'DESC',
			'post__not_in' => $this->is_search() ? array() : $exclude_posts,
			'paged'        => $paged,
		);

		if ( $this->is_search() ) {
			$tax_query_parts = array();

			$technology = $this->get_query_string_value( 'technology' );
			if ( $technology ) {
				$tax_query_parts[] = array(
					'taxonomy' => 'technology',
					'terms'    => $technology,
				);
			}

			$project_type = $this->get_query_string_value( 'project-type' );
			if ( $project_type ) {
				$tax_query_parts[] = array(
					'taxonomy' => 'project_type',
					'terms'    => $project_type,
				);
			}

			if ( ! empty( $tax_query_parts ) ) {
				$tax_query_parts['relation'] = 'AND';
				$args['tax_query'] = $tax_query_parts;
			}
		}

		$results = new WP_Query( $args );
		if ( is_array( $results->posts ) && count( $results->posts ) > 0 ) {
			return array(
				'posts'      => $this->twiggify_project_posts( $results->posts ),
				'pagination' => new Timber\Pagination( array(), $results ),
			);
		} else {
			return array();
		}
	}

	/**
	 * Transform WP posts into Twig-ready arrays.
	 */
	private function twiggify_project_posts( $project_posts ) {
		$cards = array();
		if ( ! empty( $project_posts ) ) {
			foreach ( $project_posts as $key => $post ) {
				$cards[ $key ]['_id']         = $post->ID;
				$cards[ $key ]['path']        = esc_url( get_the_permalink( $post ) );
				$cards[ $key ]['title']       = esc_html( get_the_title( $post ) );
				$cards[ $key ]['summary']     = nl2br( wp_strip_all_tags( get_the_excerpt( $post ) ) );
				$cards[ $key ]['client_name'] = esc_html( get_field( 'client_name', $post->ID ) );

				// Featured image.
				$thumbnail_id = get_post_thumbnail_id( $post->ID );
				if ( $thumbnail_id ) {
					$cards[ $key ]['image_data'] = array(
						'src' => esc_url( wp_get_attachment_image_url( $thumbnail_id, 'medium_large' ) ),
						'alt' => esc_attr( get_post_meta( $thumbnail_id, '_wp_attachment_image_alt', true ) ),
					);
				}

				// Try responsive images if available.
				if ( class_exists( 'FB_WP_HC2_Responsive_Images' ) ) {
					$listing_image = get_field( 'listing_image', $post->ID );
					if ( $listing_image ) {
						$responsive_images              = new FB_WP_HC2_Responsive_Images();
						$cards[ $key ]['image_data']     = $responsive_images->get_image_data( $listing_image, 'card_image' );
					}
				}

				// Technologies.
				$technologies = get_the_terms( $post->ID, 'technology' );
				if ( $technologies && ! is_wp_error( $technologies ) ) {
					$tech_tags = array();
					foreach ( $technologies as $tech ) {
						$tech_tags[] = array(
							'title' => esc_html( $tech->name ),
							'path'  => $this->get_term_filtered_url( $tech ),
						);
					}
					$cards[ $key ]['tags'] = $tech_tags;
				}

				// Project type label.
				$project_types = get_the_terms( $post->ID, 'project_type' );
				if ( $project_types && ! is_wp_error( $project_types ) ) {
					$cards[ $key ]['label']      = esc_html( $project_types[0]->name );
					$cards[ $key ]['label_path'] = $this->get_term_filtered_url( $project_types[0] );
				}

				// Featured flag.
				$featured = get_field( 'is_featured', $post->ID );
				if ( $featured ) {
					$cards[ $key ]['featured'] = true;
				}
			}
		}
		return $cards;
	}

	/**
	 * Get related projects by shared taxonomy terms.
	 */
	private function get_related_projects( $post_id, $limit = 3 ) {
		$technologies = get_the_terms( $post_id, 'technology' );
		if ( ! $technologies || is_wp_error( $technologies ) ) {
			return array();
		}

		$term_ids = wp_list_pluck( $technologies, 'term_id' );

		$args = array(
			'post_type'      => 'portfolio_project',
			'posts_per_page' => $limit,
			'post__not_in'   => array( $post_id ),
			'tax_query'      => array(
				array(
					'taxonomy' => 'technology',
					'terms'    => $term_ids,
				),
			),
		);

		$results = new WP_Query( $args );
		if ( is_array( $results->posts ) && count( $results->posts ) > 0 ) {
			return $this->twiggify_project_posts( $results->posts );
		}

		return array();
	}

	/**
	 * Build a filtered URL for a taxonomy term.
	 */
	private function get_term_filtered_url( $term ) {
		$base_url = get_the_permalink( PROJECTS_LISTING_PAGE );

		switch ( $term->taxonomy ) {
			case 'project_type':
				$taxonomy_query_string_name = 'project-type';
				break;
			default:
				$taxonomy_query_string_name = $term->taxonomy;
		}

		$term_filtered_url = $base_url . '?' . $taxonomy_query_string_name . '=' . $term->term_id . '&search=1';
		return $term_filtered_url;
	}
}
