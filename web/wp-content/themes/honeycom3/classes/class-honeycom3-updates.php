<?php
/**
 * Plugin Name: Honeycom3 Updates
 * Plugin URI: http://fatbeehive.com
 * Description: Class for updates post type functionality.
 * Author: Fat Beehive
 * Author URI: http://fatbeehive.com
 * Version: 1.0
 *
 * @package Honeycom3
 */

require_once get_template_directory() . '/classes/class-honeycom3-custom-post-type.php';

/**
 * Class Honeycom3_Updates
 */
class Honeycom3_Updates extends Honeycom3_Custom_Post_Type {
	/**
	 * Constructor.
	 */
	public function __construct() {
		if ( is_singular( 'update' ) ) {
			add_filter( 'timber/context', array( $this, 'add_updates_sidebar_data_to_context' ) );
		} elseif ( is_page_template( 'page-updates-listing.php' ) ) {
			add_filter( 'timber/context', array( $this, 'add_updates_listing_data_to_context' ) );
			add_filter( 'timber/context', array( $this, 'add_updates_listing_search_data_to_context' ) );
			add_filter( 'timber/context', array( $this, 'add_updates_feeds_to_context' ) );
		}
	}

	/**
	 * Add updates sidebar data to context.
	 *
	 * @param array $context the timber context array.
	 * @return array $context the timber context array with updates sidebar data.
	 */
	public function add_updates_sidebar_data_to_context( $context ) {
		$headlines = get_field( 'headlines' );
		if ( $headlines ) {
			foreach ( $headlines as $key => $headline ) {
				$context['updates_sidebar']['headlines'][ $key ]['title'] = esc_html( $headline['title'] );
			}
		}

		$context = $this->add_author_data_to_context( $context );

		$display_press_details = get_field( 'display_press_details' );
		if ( $display_press_details ) {
			$press_details                             = get_field( 'press_enquiries_details', 'option' );
			$context['updates_sidebar']['press_tel']   = esc_html( $press_details['phone_number'] );
			$context['updates_sidebar']['press_email'] = esc_html( $press_details['email'] );
			$context['updates_sidebar']['press_intro'] = esc_html( $press_details['name'] );
		}

		$update_type = get_field( 'update_type' );
		if ( $update_type ) {
			$context['updates_sidebar']['cats'] = array(
				array(
					'title' => esc_html( $update_type->name ),
					'link'  => $this->get_term_filtered_url( $update_type ),
				),
			);
		}

		$tags   = array();
		$themes = get_field( 'themes' );
		if ( $themes ) {
			foreach ( $themes as $theme ) {
				$tags[] = array(
					'title' => esc_html( $theme->name ),
					'link'  => $this->get_term_filtered_url( $theme ),
				);
			}
		}

		$topics = get_field( 'topics' );
		if ( $topics ) {
			foreach ( $topics as $topic ) {
				$tags[] = array(
					'title' => esc_html( $topic->name ),
					'link'  => $this->get_term_filtered_url( $topic ),
				);
			}
		}

		$context['updates_sidebar']['tags'] = $tags;

		return $context;
	}

	/**
	 * Add author data to context, within the updates_sidebar element.
	 *
	 * @param array $context the timber context array.
	 * @return array $context the timber context array with author data.
	 */
	private function add_author_data_to_context( $context ) {
		$author = get_field( 'author' );

		if ( 'manual' === $author['type'] ) {
			$context['updates_sidebar']['single_author'] = esc_html( $author['name'] );
		} else {
			$author_profile_id = $author['profile'];
			$responsive_images = new FB_WP_HC2_Responsive_Images();
			if ( $author_profile_id ) {
				$image_id = get_field( 'listing_image', $author_profile_id );
				$context['updates_sidebar']['author_profiles']['image_data'] = $responsive_images->get_image_data( $image_id, 'card_image' );
				$context['updates_sidebar']['author_profiles']['title']      = esc_html( get_the_title( $author_profile_id ) );
				$context['updates_sidebar']['author_profiles']['role']       = get_field( 'role', $author_profile_id );

				$contact_channels = get_field( 'contact_channels', $author_profile_id );
				if ( $contact_channels['email'] ) {
					$context['updates_sidebar']['author_profiles']['author_info']['email']['title']    = esc_html( $contact_channels['email'] );
					$context['updates_sidebar']['author_profiles']['author_info']['email']['selector'] = 'mail';
					$context['updates_sidebar']['author_profiles']['author_info']['email']['link']     = 'mailto:' . esc_html( $contact_channels['email'] );
				}

				if ( $contact_channels['twitter'] ) {
					$context['updates_sidebar']['author_profiles']['author_info']['twitter']['title']    = 'Twitter';
					$context['updates_sidebar']['author_profiles']['author_info']['twitter']['selector'] = 'twitter';
					$context['updates_sidebar']['author_profiles']['author_info']['twitter']['link']     = esc_url( $contact_channels['twitter'] );
				}

				if ( $contact_channels['facebook'] ) {
					$context['updates_sidebar']['author_profiles']['author_info']['facebook']['title']    = 'Facebook';
					$context['updates_sidebar']['author_profiles']['author_info']['facebook']['selector'] = 'facebook';
					$context['updates_sidebar']['author_profiles']['author_info']['facebook']['link']     = esc_url( $contact_channels['facebook'] );
				}

				if ( $contact_channels['linkedin'] ) {
					$context['updates_sidebar']['author_profiles']['author_info']['linkedin']['title']    = 'Linkedin';
					$context['updates_sidebar']['author_profiles']['author_info']['linkedin']['selector'] = 'linkedin';
					$context['updates_sidebar']['author_profiles']['author_info']['linkedin']['link']     = esc_url( $contact_channels['linkedin'] );
				}

				if ( $contact_channels['website'] ) {
					$context['updates_sidebar']['author_profiles']['author_info']['website']['title']    = 'Website';
					$context['updates_sidebar']['author_profiles']['author_info']['website']['selector'] = 'link';
					$context['updates_sidebar']['author_profiles']['author_info']['website']['link']     = esc_url( $contact_channels['website'] );
				}
			}
		}
		return $context;
	}

	/**
	 * Add data for search/filters.
	 *
	 * @param array $context the timber context array.
	 * @return array $context the timber context array with search/filters data.
	 */
	public function add_updates_listing_search_data_to_context( $context ) {
		$context['search']['update_types']  = $this->get_taxonomy_terms( 'update-type' );
		$context['search']['update_topics'] = $this->get_taxonomy_terms( 'topic' );
		$context['search']['update_themes'] = $this->get_taxonomy_terms( 'themes' );

		if ( $this->is_search() ) {
			$update_type                         = $this->get_query_string_value( 'update-type' );
			$context['selected']['update_type']  = $update_type;
			$update_topic                        = $this->get_query_string_value( 'update-topic' );
			$context['selected']['update_topic'] = $update_topic;
			$update_theme                        = $this->get_query_string_value( 'update-theme' );
			$context['selected']['update_theme'] = $update_theme;
		}
		return $context;
	}

	/**
	 * Add updates listing data to context.
	 *
	 * @param array $context the timber context array.
	 * @return array $context the timber context array with updates listing data.
	 */
	public function add_updates_listing_data_to_context( $context ) {
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
	 * Add updates feed data to context.
	 *
	 * @param array $context the timber context array.
	 * @return array $context the timber context array with updates feed data.
	 */
	public function add_updates_feeds_to_context( $context ) {
		$featured_posts     = $this->get_featured_updates();
		$featured_posts_ids = array_column( $featured_posts, '_id' );
		$posts              = $this->get_updates( $featured_posts_ids );

		$context['featured_cards'] = $featured_posts;
		$context['updates_feed']   = $posts['posts'];
		$context['pagination']     = $posts['pagination'];
		return $context;
	}

	/**
	 * Get featured update posts.
	 *
	 * @param int $posts_per_page the max number of featured posts to get.
	 * @return array $posts the featured update posts.
	 */
	private function get_featured_updates( $posts_per_page = 3 ) {
		// No featured posts when filtering.
		if ( $this->is_search() ) {
			return array();
		}

		$args    = array(
			'post_type'      => 'update',
			'posts_per_page' => $posts_per_page,
			'meta_query'     => array(
				array(
					'key'   => 'featured_update',
					'value' => 1,
				),
			),
		);
		$results = new WP_Query( $args );
		if ( is_array( $results->posts ) && count( $results->posts ) > 0 ) {
			return $this->twiggify_update_posts( $results->posts );
		} else {
			return array();
		}
	}

	/**
	 * Get update posts.
	 *
	 * @param array $exclude_posts ID of all posts to exclude from query.
	 * @return array $posts the update posts.
	 */
	private function get_updates( $exclude_posts ) {
		$paged = get_query_var( 'paged', 1 );
		$args  = array(
			'post_type'    => 'update',
			'post__not_in' => $exclude_posts,
			'paged'        => $paged,
		);

		if ( $this->is_search() ) {
			$tax_query_parts = array();
			$update_type     = $this->get_query_string_value( 'update-type' );
			if ( $update_type ) {
				$tax_query_parts[] = array(
					'taxonomy' => 'update-type',
					'terms'    => $update_type,
				);
			}
			$update_topic = $this->get_query_string_value( 'update-topic' );
			if ( $update_topic ) {
				$tax_query_parts[] = array(
					'taxonomy' => 'topic',
					'terms'    => $update_topic,
				);
			}
			$update_theme = $this->get_query_string_value( 'update-theme' );
			if ( $update_theme ) {
				$tax_query_parts[] = array(
					'taxonomy' => 'themes',
					'terms'    => $update_theme,
				);
			}
			if ( ! empty( $tax_query_parts ) ) {
				$args['tax_query'] = array(
					'relation' => 'AND',
					$tax_query_parts,
				);

			}
		}

		$results = new WP_Query( $args );
		if ( is_array( $results->posts ) && count( $results->posts ) > 0 ) {
			return array(
				'posts'      => $this->twiggify_update_posts( $results->posts ),
				'pagination' => new Timber\Pagination( array(), $results ),
			);
		} else {
			return array();
		}
	}

	/**
	 * Twiggify update posts data.
	 *
	 * @param array $update_posts update post objects.
	 * @return array $cards post update data formatted for twig.
	 */
	private function twiggify_update_posts( $update_posts ) {
		$cards             = array();
		$responsive_images = new FB_WP_HC2_Responsive_Images();
		if ( ! empty( $update_posts ) ) {
			foreach ( $update_posts as $key => $post ) {
				$cards[ $key ]['_id']        = $post->ID;
				$cards[ $key ]['path']       = esc_url( get_the_permalink( $post ) );
				$cards[ $key ]['image_data'] = $responsive_images->get_image_data( get_field( 'listing_image', $post->ID ), 'card_image' );
				$cards[ $key ]['label']      = $this->get_update_type_name( $post );
				$cards[ $key ]['label_path'] = $this->get_term_filtered_url( get_field( 'update_type', $post->ID ) );
				$cards[ $key ]['title']      = esc_html( get_the_title( $post ) );
				$cards[ $key ]['date']       = get_the_date( 'U', $post->ID );
				$cards[ $key ]['summary']    = nl2br( wp_strip_all_tags( get_field( 'listing_summary', $post->ID ) ) );
				$cards[ $key ]['tags']       = $this->get_update_tags( $post );
				$featured                    = get_field( 'featured_update', $post->ID );
				if ( $featured ) {
					$cards[ $key ]['featured'] = true;
				}
			}
		}
		return $cards;
	}

	/**
	 * Get tags an update post.
	 *
	 * @param object $post update post object.
	 * @return array $tags tags data for twig.
	 */
	private function get_update_tags( $post ) {
		$tags          = array();
		$update_themes = get_field( 'themes', $post->ID );
		$update_topics = get_field( 'topics', $post->ID );
		$i             = 0;

		if ( $update_themes ) {
			foreach ( $update_themes as $key => $theme ) {
				$tags[ $i ]['path']  = $this->get_term_filtered_url( $theme );
				$tags[ $i ]['title'] = $theme->name;
				++$i;
			}
		}

		if ( $update_topics ) {
			foreach ( $update_topics as $key => $topic ) {
				$tags[ $i ]['path']  = $this->get_term_filtered_url( $topic );
				$tags[ $i ]['title'] = $topic->name;
				++$i;
			}
		}
		return $tags;
	}

	/**
	 * Get the update type name.
	 *
	 * @param object $post update post object.
	 * @return string $term_name the update type name.
	 */
	private function get_update_type_name( $post ) {
		$update_type = get_field( 'update_type', $post->ID );
		if ( $update_type && isset( $update_type->name ) ) {
			$update_type_name = $update_type->name;
			return $update_type_name;
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
		$base_url = get_the_permalink( UPDATES_LISTING_PAGE );
		switch ( $term->taxonomy ) {
			case 'topic':
				$taxonomy_query_string_name = 'update-topic';
				break;

			case 'themes':
				$taxonomy_query_string_name = 'update-theme';
				break;

			default:
				$taxonomy_query_string_name = $term->taxonomy;
		}
		$term_filtered_url = $base_url . '?' . $taxonomy_query_string_name . '=' . $term->term_id . '&search=1';
		return $term_filtered_url;
	}
}
