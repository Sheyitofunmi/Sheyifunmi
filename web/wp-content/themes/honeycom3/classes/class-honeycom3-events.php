<?php
/**
 * Plugin Name: Honeycom3 Events
 * Plugin URI: http://fatbeehive.com
 * Description: Class for event post type functionality.
 * Author: Fat Beehive
 * Author URI: http://fatbeehive.com
 * Version: 1.0
 *
 * @package Honeycom3
 */

require_once get_template_directory() . '/classes/class-honeycom3-custom-post-type.php';

/**
 * Class Honeycom3_Event
 */
class Honeycom3_Events extends Honeycom3_Custom_Post_Type {
	/**
	 * Constructor.
	 */
	public function __construct() {
		if ( is_singular( 'event' ) ) {
			add_filter( 'timber/context', array( $this, 'add_event_sidebar_data_to_context' ) );
		} elseif ( is_page_template( 'page-event-listing.php' ) ) {
			add_filter( 'timber/context', array( $this, 'add_event_listing_data_to_context' ) );
			add_filter( 'timber/context', array( $this, 'add_event_listing_search_data_to_context' ) );
			add_filter( 'timber/context', array( $this, 'add_events_feeds_to_context' ) );
		}
	}

	/**
	 * Add event sidebar data to context.
	 *
	 * @param array $context the timber context array.
	 * @return array $context the timber context array with event sidebar data.
	 */
	public function add_event_sidebar_data_to_context( $context ) {
		$event_type = get_field( 'event_type' );
		if ( $event_type ) {
			$context['events_sidebar']['cats'] = array(
				array(
					'title' => esc_html( $event_type->name ),
					'link'  => $this->get_term_filtered_url( $event_type ),
				),
			);
		}

		$tags           = array();
		$event_category = get_field( 'event_category' );
		if ( $event_category ) {
			$tags[] = array(
				'title' => esc_html( $event_category->name ),
				'link'  => $this->get_term_filtered_url( $event_category ),
			);
		}

		$event_location = get_field( 'event_location' );
		if ( $event_location ) {
			$tags[] = array(
				'title' => esc_html( $event_location->name ),
				'link'  => $this->get_term_filtered_url( $event_location ),
			);
		}

		$context['events_sidebar']['tags'] = $tags;

		$address = get_field( 'address', get_field( 'event_location' ) );
		if ( $address && ! empty( $address['address'] ) ) {
			$context['events_sidebar']['location'] = esc_html( $address['address'] );
		}

		$registration_link = get_field( 'registration_link' );
		if ( $registration_link ) {
			$context['events_sidebar']['postbuttons'][] = array(
				'title'    => esc_html( $registration_link['title'] ),
				'link'     => esc_url( $registration_link['url'] ),
				'external' => '_blank' === $registration_link['target'] ? true : false,
			);
		}

		return $context;
	}

	/**
	 * Add data for search/filters.
	 *
	 * @param array $context the timber context array.
	 * @return array $context the timber context array with search/filters data.
	 */
	public function add_event_listing_search_data_to_context( $context ) {
		$context['search']['event_types']      = $this->get_taxonomy_terms( 'event-type' );
		$context['search']['event_categories'] = $this->get_taxonomy_terms( 'event-category' );
		$context['search']['event_locations']  = $this->get_taxonomy_terms( 'location' );

		if ( $this->is_search() ) {
			$event_type                            = $this->get_query_string_value( 'event-type' );
			$context['selected']['event_type']     = $event_type;
			$event_category                        = $this->get_query_string_value( 'event-category' );
			$context['selected']['event_category'] = $event_category;
			$event_location                        = $this->get_query_string_value( 'event-location' );
			$context['selected']['event_location'] = $event_location;
			$context['selected']['date_from']      = $this->get_query_string_value( 'date-from' );
			$context['selected']['date_to']        = $this->get_query_string_value( 'date-to' );
		}
		return $context;
	}

	/**
	 * Add event listing data to context.
	 *
	 * @param array $context the timber context array.
	 * @return array $context the timber context array with event listing data.
	 */
	public function add_event_listing_data_to_context( $context ) {
		$hero_metas      = array();
		$contact_details = get_field( 'contact_details_section' );

		if ( ! empty( $contact_details['telephone_number']['telephone_number'] ) && ! empty( $contact_details['telephone_number']['label'] ) ) {
			$hero_metas['telephone']['title'] = esc_html( $contact_details['telephone_number']['label'] );
			$hero_metas['telephone']['info']  = esc_html( $contact_details['telephone_number']['telephone_number'] );
		}

		if ( ! empty( $contact_details['email_address'] ) ) {
			$hero_metas['email']['title'] = 'Email: ';
			$hero_metas['email']['info']  = esc_html( $contact_details['email_address'] );
		}

		$context['hero_metas'] = $hero_metas;
		return $context;
	}

	/**
	 * Add events feed data to context.
	 *
	 * @param array $context the timber context array.
	 * @return array $context the timber context array with event feed data.
	 */
	public function add_events_feeds_to_context( $context ) {
		$featured_posts     = $this->get_featured_events();
		$featured_posts_ids = array_column( $featured_posts, '_id' );
		$posts              = $this->get_events( $featured_posts_ids );

		$context['featured_cards'] = $featured_posts;
		$context['events_feed']    = isset( $posts['posts'] ) ? $posts['posts'] : false;
		$context['pagination']     = isset( $posts['pagination'] ) ? $posts['pagination'] : false;
		return $context;
	}

	/**
	 * Get featured event posts.
	 *
	 * @param int $posts_per_page the max number of featured posts to get.
	 * @return array $posts the featured event posts.
	 */
	private function get_featured_events( $posts_per_page = 3 ) {
		// No featured posts when filtering.
		if ( $this->is_search() ) {
			return array();
		}

		$args    = array(
			'post_type'      => 'event',
			'posts_per_page' => $posts_per_page,
			'meta_key'       => 'start_date',
			'orderby'        => 'meta_value_num',
			'order'          => 'ASC',
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'key'   => 'featured_event',
					'value' => 1,
				),
				array(
					'key'     => 'start_date',
					// use server date instead of GMT.
					'value'   => date( 'Ymd' ), // phpcs:ignore WordPress.DateTime.RestrictedFunctions
					'compare' => '>=',
				),
			),
		);
		$results = new WP_Query( $args );
		if ( is_array( $results->posts ) && count( $results->posts ) > 0 ) {
			return $this->twiggify_event_posts( $results->posts );
		} else {
			return array();
		}
	}

	/**
	 * Get event posts.
	 *
	 * @param array $exclude_posts ID of all posts to exclude from query.
	 * @return array $posts the event posts.
	 */
	private function get_events( $exclude_posts ) {
		$paged = get_query_var( 'paged', 1 );
		$args  = array(
			'post_type'    => 'event',
			'meta_key'     => 'start_date',
			'orderby'      => 'meta_value_num',
			'order'        => 'ASC',
			'post__not_in' => $this->is_search() ? array() : $exclude_posts,
			'paged'        => $paged,
			'meta_query'   => array(
				'relation' => 'OR',
				array(
					'key'     => 'start_date',
					'value'   => date( 'Ymd' ), // use server date
					'compare' => '>=',
					'type'    => 'NUMERIC',
				),
				array(
					'key'     => 'end_date',
					'value'   => date( 'Ymd' ),
					'compare' => '>=',
					'type'    => 'NUMERIC',
				),
			),
		);

		if ( $this->is_search() ) {
			$tax_query_parts = array();
			$event_type      = $this->get_query_string_value( 'event-type' );
			if ( $event_type ) {
				$tax_query_parts[] = array(
					'taxonomy' => 'event-type',
					'terms'    => $event_type,
				);
			}
			$event_category = $this->get_query_string_value( 'event-category' );
			if ( $event_category ) {
				$tax_query_parts[] = array(
					'taxonomy' => 'event-category',
					'terms'    => $event_category,
				);
			}
			$event_location = $this->get_query_string_value( 'event-location' );
			if ( $event_location ) {
				$tax_query_parts[] = array(
					'taxonomy' => 'location',
					'terms'    => $event_location,
				);
			}
			if ( ! empty( $tax_query_parts ) ) {
				$args['tax_query'] = array(
					'relation' => 'AND',
					$tax_query_parts,
				);

			}
			$date_from        = $this->get_query_string_value( 'date-from' );
			$date_to          = $this->get_query_string_value( 'date-to' );
			$meta_query_parts = array();

			if ( ! empty( $date_from ) && ! empty( $date_to ) ) {
				// use server date instead of GMT.
				$formatted_date_from = date( 'Ymd', strtotime( $date_from ) ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions
				$formatted_date_to   = date( 'Ymd', strtotime( $date_to ) ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions
				$meta_query_parts    = array(
					'relation' => 'OR',
					// event end date fits between date from and date to entered.
					array(
						'relation' => 'AND',
						array(
							'key'     => 'end_date',
							'value'   => $formatted_date_from,
							'compare' => '>=',
						),
						array(
							'key'     => 'end_date',
							'value'   => $formatted_date_to,
							'compare' => '<=',
						),
					),
					// or event start date fits between date from and date to entered.
					array(
						'relation' => 'AND',
						array(
							'key'     => 'start_date',
							'value'   => $formatted_date_from,
							'compare' => '>=',
						),
						array(
							'key'     => 'start_date',
							'value'   => $formatted_date_to,
							'compare' => '<=',
						),
					),
				);
			} elseif ( ! empty( $date_to ) ) {
				// use server date instead of GMT.
				$formatted_date_to  = date( 'Ymd', strtotime( $date_to ) ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions
				$meta_query_parts[] = $this->get_meta_query_parts_for_user_entered_date( $formatted_date_to );
			} elseif ( ! empty( $date_from ) ) {
				// use server date instead of GMT.
				$formatted_date_from = date( 'Ymd', strtotime( $date_from ) ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions
				$meta_query_parts[]  = $this->get_meta_query_parts_for_user_entered_date( $formatted_date_from );
			}

			if ( ! empty( $meta_query_parts ) ) {
				$args['meta_query'] = $meta_query_parts;
			}
		}

		$results = new WP_Query( $args );
		if ( is_array( $results->posts ) && count( $results->posts ) > 0 ) {
			return array(
				'posts'      => $this->twiggify_event_posts( $results->posts ),
				'pagination' => new Timber\Pagination( array(), $results ),
			);
		} else {
			return array();
		}
	}

	/**
	 * Generate meta_query arguments required to find events where the user entered date is either
	 * in between the event start date and the event end date or
	 * equal to the event start date and the event has no end date.
	 *
	 * @param array $user_entered_date user entered date in Ymd format.
	 * @return array $meta_query_parts the meta_query arguments.
	 */
	private function get_meta_query_parts_for_user_entered_date( $user_entered_date ) {
		$meta_query_parts[] = array(
			'relation' => 'OR',
			// user entered date is between event start date and event end date.
			array(
				'relation' => 'AND',
				array(
					'key'     => 'start_date',
					'value'   => $user_entered_date,
					'compare' => '<=',
				),
				array(
					'key'     => 'end_date',
					'value'   => $user_entered_date,
					'compare' => '>=',
				),
			),
			// OR user entered date is equal to event start date and event end date is blank.
			array(
				'relation' => 'AND',
				array(
					'key'     => 'start_date',
					'value'   => $user_entered_date,
					'compare' => '=',
				),
				array(
					'key'     => 'end_date',
					'value'   => '',
					'compare' => '=',
				),
			),
		);
		return $meta_query_parts;
	}

	/**
	 * Get summary location data for an event post.
	 *
	 * @param array $event_posts event post objects.
	 * @return array $cards event data formatted for twig.
	 */
	private function twiggify_event_posts( $event_posts ) {
		$cards             = array();
		$responsive_images = new FB_WP_HC2_Responsive_Images();
		if ( ! empty( $event_posts ) ) {
			foreach ( $event_posts as $key => $post ) {
				$cards[ $key ]['_id']        = $post->ID;
				$cards[ $key ]['path']       = esc_url( get_the_permalink( $post ) );
				$cards[ $key ]['image_data'] = $responsive_images->get_image_data( get_field( 'listing_image', $post->ID ), 'card_image' );
				$cards[ $key ]['label']      = $this->get_event_type_name( $post );
				$cards[ $key ]['label_path'] = $this->get_term_filtered_url( get_field( 'event_type', $post->ID ) );
				$cards[ $key ]['title']      = esc_html( get_the_title( $post ) );
				$cards[ $key ]['startdate']  = esc_html( strtotime( get_field( 'start_date', $post->ID ) ) );
				$cards[ $key ]['enddate']    = esc_html( strtotime( get_field( 'end_date', $post->ID ) ) );
				$cards[ $key ]['startTime']  = esc_html( get_field( 'start_time', $post->ID ) );
				$cards[ $key ]['endTime']    = esc_html( get_field( 'end_time', $post->ID ) );
				$cards[ $key ]['location']   = esc_html( $this->get_event_location_short_name( $post ) );
				$cards[ $key ]['summary']    = nl2br( wp_strip_all_tags( get_field( 'listing_summary', $post->ID ) ) );
				$cards[ $key ]['tags']       = $this->get_event_tags( $post );
				$featured                    = get_field( 'featured_event', $post->ID );
				if ( $featured ) {
					$cards[ $key ]['featured'] = true;
				}
			}
		}
		return $cards;
	}

	/**
	 * Get summary location data for an event post.
	 *
	 * @param object $post event post object.
	 * @return string $short_name short name for the location.
	 */
	private function get_event_location_short_name( $post ) {
		$location_term = get_field( 'event_location', $post->ID );
		if ( $location_term ) {
			$location_data = get_field( 'address', $location_term );
			if ( $location_data ) {
				$location_parts = array_filter( array( $location_data['city'], $location_data['country'] ) );
				$short_name     = implode( ', ', $location_parts );
				return $short_name;
			}
		}
	}

	/**
	 * Get tags an event post.
	 *
	 * @param object $post event post object.
	 * @return array $tags tags data for twig.
	 */
	private function get_event_tags( $post ) {
		$tags          = array();
		$category_term = get_field( 'event_category', $post->ID );
		$location_term = get_field( 'event_location', $post->ID );

		if ( $category_term ) {
			$tags['category']['path']  = $this->get_term_filtered_url( $category_term );
			$tags['category']['title'] = $category_term->name;
		}

		if ( $location_term ) {
			$tags['location']['path']  = $this->get_term_filtered_url( $location_term );
			$tags['location']['title'] = $location_term->name;
		}
		return $tags;
	}

	/**
	 * Get the event type name.
	 *
	 * @param object $post event post object.
	 * @return string $term_name the event type name.
	 */
	private function get_event_type_name( $post ) {
		$event_type = get_field( 'event_type', $post->ID );
		if ( $event_type && isset( $event_type->name ) ) {
			$event_type_name = $event_type->name;
			return $event_type_name;
		} else {
			return false;
		}
	}

	/**
	 * Get the URL for the pre-filtered listing results.
	 *
	 * @param object $term term object.
	 * @return string $term_filtered_url the url with the correct query strings to filter by the $term.
	 */
	private function get_term_filtered_url( $term ) {
		$base_url = get_the_permalink( EVENTS_LISTING_PAGE );
		switch ( $term->taxonomy ) {
			case 'location':
				$taxonomy_query_string_name = 'event-location';
				break;

			default:
				$taxonomy_query_string_name = $term->taxonomy;
		}
		$term_filtered_url = $base_url . '?' . $taxonomy_query_string_name . '=' . $term->term_id . '&search=1';
		return $term_filtered_url;
	}
}
