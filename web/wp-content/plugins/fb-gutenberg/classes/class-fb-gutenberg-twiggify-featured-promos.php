<?php
/**
 * Plugin Name: FB Gutenberg
 * Plugin URI: http://fatbeehive.com
 * Description: Class for handling featured promos data processing and formatting for twig/timber.
 * Author: Fat Beehive
 * Author URI: http://fatbeehive.com
 * Version: 1.0
 *
 * @package FB Gutenberg
 */

/**
 * Class FB_Gutenberg_Twiggify_Featured_Promos
 */
class FB_Gutenberg_Twiggify_Featured_Promos {
	/**
	 * Twiggify the data for featured promo items.
	 *
	 * @param array $fields ACF block fields data.
	 * @return array $cards Twiggified data as expected by FE template.
	 */
	public function twiggify_featured_promos_items( $fields ) {
		$type = $fields['type'];

		if ( 'manual' === $type ) {
			if ( 'cms' === $fields['manual_promos']['type'] ) {
				$cards = $this->twiggify_manual_featured_promos_items_from_cms( $fields );
			} elseif ( 'manual' === $fields['manual_promos']['type'] ) {
				$cards = $this->twiggify_manual_featured_promos_items_from_fields( $fields );
			}
		} elseif ( 'automatic' === $type ) {
			$automatic_promos = $this->get_automatic_featured_promos_items( $fields );
			$cards            = $this->twiggify_automatic_featured_promos_items( $automatic_promos, $fields );
		}
		return $cards;
	}

	/**
	 * Twiggify manual featured promos repeater's items for CMS promos.
	 *
	 * @param array $fields ACF block fields data.
	 * @return array $cards Twiggified data as expected by FE template.
	 */
	private function twiggify_manual_featured_promos_items_from_cms( $fields ) {
		// Bail.
		if ( empty( $fields['manual_promos']['selected_content'] ) ) {
			return false;
		}

		$responsive_images = class_exists( 'FB_WP_HC2_Responsive_Images' ) ? new FB_WP_HC2_Responsive_Images() : null;

		$twiggified_items = false;

		$show_all_fields = ! $fields['display_title_only'];

		foreach ( $fields['manual_promos']['selected_content'] as $item ) {
			if ( isset( $item['override_fields'] ) && $item['override_fields'] ) {
				$formatted_item['path'] = get_the_permalink( $item['page'] );

				// Fields that can be overridden.
				$formatted_item['title'] = $item['title_override'] ? $item['title_override'] : get_the_title( $item['page'] );
				if ( $show_all_fields ) {
					$image_id                     = $item['image_override'] ? $item['image_override'] : get_field( 'listing_image', $item['page'] );
					$formatted_item['image_data'] = $responsive_images->get_image_data( $image_id, 'card_image' ) ?? false;
					$formatted_item['summary']    = $item['summary_override'] ? $item['summary_override'] : get_field( 'listing_summary', $item['page'] );
				}
			} else {
				$formatted_item['path']  = get_the_permalink( $item['page'] );
				$formatted_item['title'] = get_the_title( $item['page'] );
				if ( $show_all_fields ) {
					$formatted_item['button']  = get_the_title( $item['page'] );
					$formatted_item['summary'] = get_field( 'listing_summary', $item['page'] );
					$image_id                  = get_field( 'listing_image', $item['page'] );
					if ( $image_id ) {
						$formatted_item['image_data'] = $responsive_images->get_image_data( $image_id, 'card_image' );
					} else {
						// Try hero image as fallback.
						$image_id                     = get_field( 'hero_image', $item['page'] );
						$formatted_item['image_data'] = $responsive_images->get_image_data( $image_id, 'card_image' );
					}
				}
			}

			$formatted_item = apply_filters( 'override_manual_featured_promo_item', $formatted_item, $fields );
			$cards[]        = $formatted_item;
			unset( $formatted_item );
		}

		return $cards;
	}

	/**
	 * Twiggify manual featured promos repeater's items for manually input promos.
	 *
	 * @param array $fields ACF block fields data.
	 * @return array $cards Twiggified data as expected by FE template.
	 */
	public function twiggify_manual_featured_promos_items_from_fields( $fields ) {
		// Bail.
		if ( empty( $fields['manual_promos']['manual_content'] ) ) {
			return false;
		}

		$responsive_images = class_exists( 'FB_WP_HC2_Responsive_Images' ) ? new FB_WP_HC2_Responsive_Images() : null;
		$show_all_fields   = ! $fields['display_title_only'];

		$twiggified_items = false;
		foreach ( $fields['manual_promos']['manual_content'] as $item ) {
			$formatted_item['title']  = $item['title'] ?? '';
			$formatted_item['path']   = $item['button']['url'] ?? '';
			$formatted_item['target'] = $item['button']['target'] ?? '';
			if ( $show_all_fields ) {
				$formatted_item['summary'] = $item['summary'] ?? '';

				// Image.
				$image_id                     = $item['image'] ?? false;
				$formatted_item['image_data'] = $responsive_images->get_image_data( $image_id, 'card_image' ) ?? false;

				// Item link button.
				$formatted_item['button'] = $item['button']['title'] ?? '';
			}
			$formatted_item = apply_filters( 'override_manual_featured_promo_item', $formatted_item, $fields );
			$cards[]        = $formatted_item;
			unset( $formatted_item );
		}

		return $cards;
	}

	/**
	 * Run WP_Query to fetch content as specified by feed criteria in $fields.
	 *
	 * @param array $fields ACF block fields data.
	 * @return array $automatic_promos posts matching field criteria specified in ACF.
	 */
	protected function get_automatic_featured_promos_items( $fields ) {
		$automatic_promos = array();
		$args             = array();

		$feed_criteria = $fields['automatic_promos'];

		if ( 'profiles' === $feed_criteria['content_type'] ) {
			return $this->get_profile_pages( $fields );
		}

		$args['posts_per_page'] = 'all' === $feed_criteria['quantity'] ? 50 : $feed_criteria['quantity'];
		$args['post_type']      = $feed_criteria['content_type'];
		$args['orderby']        = $feed_criteria['sort_by'];

		switch ( $args['orderby'] ) {
			case 'event_dates_date_from':
				$args['meta_key'] = 'event_dates_date_from';
				$args['order']    = 'DESC';
				$args['orderby']  = 'meta_value_num';
				break;

			default:
				if ( 'title' === $feed_criteria['sort_by'] ) {
					$args['order'] = 'ASC';
				} else {
					$args['order'] = 'DESC';
				}
				break;
		}

		$taxonomies = array_filter( $feed_criteria['taxonomies'] );
		if ( ! empty( $taxonomies ) ) {
			$tax_query_parts = array();
			foreach ( $taxonomies as $taxonomy => $terms ) {
				$terms_to_query = array();
				switch ( $taxonomy ) {
					case 'update_types':
						if ( 'update' === $feed_criteria['content_type'] ) {
							$wp_taxonomy_name = 'update-type';
							$terms_to_query   = array_merge( $terms_to_query, $terms );
						}
						break;

					case 'update_topics':
						if ( 'update' === $feed_criteria['content_type'] ) {
							$wp_taxonomy_name = 'topic';
							$terms_to_query   = array_merge( $terms_to_query, $terms );
						}
						break;

					case 'update_themes':
						if ( 'update' === $feed_criteria['content_type'] ) {
							$wp_taxonomy_name = 'themes';
							$terms_to_query   = array_merge( $terms_to_query, $terms );
						}
						break;

					case 'event_categories':
						if ( 'event' === $feed_criteria['content_type'] ) {
							$wp_taxonomy_name = 'event-category';
							$terms_to_query   = array_merge( $terms_to_query, $terms );
						}
						break;

					case 'event_types':
						if ( 'event' === $feed_criteria['content_type'] ) {
							$wp_taxonomy_name = 'event-type';
							$terms_to_query   = array_merge( $terms_to_query, $terms );
						}
						break;

					case 'event_locations':
						if ( 'event' === $feed_criteria['content_type'] ) {
							$wp_taxonomy_name = 'location';
							$terms_to_query   = array_merge( $terms_to_query, $terms );
						}
						break;

					case 'library_types':
						if ( 'library' === $feed_criteria['content_type'] ) {
							$wp_taxonomy_name = 'library-type';
							$terms_to_query   = array_merge( $terms_to_query, $terms );
						}
						break;

					case 'views_type':
							if ( 'views' === $feed_criteria['content_type'] ) {
								$wp_taxonomy_name = 'views-type';
								$terms_to_query   = array_merge( $terms_to_query, $terms );
							}
							break;
							
					case 'views_topic':
						if ( 'views' === $feed_criteria['content_type'] ) {
							$wp_taxonomy_name = 'topic';
							$terms_to_query   = array_merge( $terms_to_query, $terms );
						}
						break;

					case 'views_theme':
						if ( 'views' === $feed_criteria['content_type'] ) {
							$wp_taxonomy_name = 'themes';
							$terms_to_query   = array_merge( $terms_to_query, $terms );
						}
						break;

					default:
						$wp_taxonomy_name = $taxonomy;
				}

				if ( $terms_to_query ) {
					$tax_query_parts[] = array(
						'taxonomy' => $wp_taxonomy_name,
						'field'    => 'term_id',
						'terms'    => $terms_to_query,
						'operator' => 'IN',
					);
				}

				if ( 'site_wide_tags' === $taxonomy ) {
					$tax_query_parts[] = array(
						'taxonomy' => 'site_wide_tag',
						'field'    => 'term_id',
						'terms'    => $terms,
						'operator' => 'IN',
					);
				}
			}

			$tax_query_parts = apply_filters( 'override_automatic_featured_promos_tax_query_parts', $tax_query_parts, $feed_criteria );

			if ( $tax_query_parts ) {
				$args['tax_query'] = array_merge( array( 'relation' => 'AND' ), $tax_query_parts );
			}
		}

        $args = apply_filters( 'override_automatic_featured_promos_args', $args, $feed_criteria );

		$results = new WP_Query( $args );

		if ( isset( $results->posts ) && ! empty( $results->posts ) ) {
			$automatic_promos = $results->posts;
		}

		return $automatic_promos;
	}

	/**
	 * Twiggify automatic featured promo items.
	 *
	 * @param array $promo_items posts.
	 * @return array  $twiggified_items Twiggified data as expected by FE template..
	 */
	public function twiggify_automatic_featured_promos_items( $promo_items, $fields ) {
		// Bail.
		if ( empty( $promo_items ) ) {
			return false;
		}

		$cards             = array();
		$responsive_images = class_exists( 'FB_WP_HC2_Responsive_Images' ) ? new FB_WP_HC2_Responsive_Images() : null;
		$show_all_fields   = ! $fields['display_title_only'];

		foreach ( $promo_items as $post ) {
			$formatted_item['title'] = get_the_title( $post->ID );
			$formatted_item['path']  = get_the_permalink( $post->ID );
			if ( $show_all_fields ) {
				$image_id                     = get_field( 'listing_image', $post->ID );
				$formatted_item['image_data'] = $responsive_images->get_image_data( $image_id, 'card_image' ) ?? null;
				$formatted_item['button']     = get_the_title( $post->ID );
				$formatted_item['summary']    = get_field( 'listing_summary', $post->ID );
			}

			$formatted_item = apply_filters( 'override_automatic_featured_promo_item', $formatted_item, $post );

			$cards[] = $formatted_item;
			unset( $formatted_item );
		}
		return $cards;
	}

	/**
	 * Run WP_Query to fetch content as specified by feed criteria in $fields.
	 *
	 * @param array $fields ACF block fields data.
	 * @return array $pages profile page posts matching field criteria specified in ACF.
	 */
	private function get_profile_pages( $fields ) {
		$pages         = array();
		$feed_criteria = $fields['automatic_promos'];

		$args['posts_per_page'] = 'all' === $feed_criteria['quantity'] ? 50 : $feed_criteria['quantity'];
		$args['post_type']      = 'page';
		$args['meta_key']       = '_wp_page_template';
		$args['meta_value']     = 'page-profiles.php';
		$args['orderby']        = $feed_criteria['sort_by'];

		$taxonomies = array_filter( $feed_criteria['taxonomies'] );
		if ( ! empty( $taxonomies ) ) {
			$tax_query_parts = array();
			foreach ( $taxonomies as $taxonomy => $terms ) {
				$terms_to_query = array();
				switch ( $taxonomy ) {
					case 'profile_types':
						if ( 'profiles' === $feed_criteria['content_type'] ) {
							$wp_taxonomy_name = 'profile-type';
							$terms_to_query   = array_merge( $terms_to_query, $terms );
						}
						break;

					default:
						$wp_taxonomy_name = $taxonomy;
				}

				if ( $terms_to_query ) {
					$tax_query_parts[] = array(
						'taxonomy' => $wp_taxonomy_name,
						'field'    => 'term_id',
						'terms'    => $terms_to_query,
						'operator' => 'IN',
					);
				}
			}

			if ( $tax_query_parts ) {
				$args['tax_query'] = array_merge( array( 'relation' => 'AND' ), $tax_query_parts );
			}
		}

		$results = new WP_Query( $args );

		if ( isset( $results->posts ) && ! empty( $results->posts ) ) {
			$pages = $results->posts;
		}

		return $pages;
	}
}
