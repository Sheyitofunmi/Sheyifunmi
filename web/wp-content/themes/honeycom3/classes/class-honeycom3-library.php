<?php
/**
 * Plugin Name: Honeycom3 Library
 * Plugin URI: http://fatbeehive.com
 * Description: Class for library post type functionality.
 * Author: Fat Beehive
 * Author URI: http://fatbeehive.com
 * Version: 1.0
 *
 * @package Honeycom3
 */

require_once get_template_directory() . '/classes/class-honeycom3-custom-post-type.php';

/**
 * Class Honeycom3_Library
 */
class Honeycom3_Library extends Honeycom3_Custom_Post_Type {
	/**
	 * Constructor.
	 */
	public function __construct() {
		if ( is_singular( 'library' ) ) {
			add_filter( 'timber/context', array( $this, 'add_library_sidebar_data_to_context' ) );
		} elseif ( is_page_template( 'page-library-listing.php' ) ) {
			add_filter( 'timber/context', array( $this, 'add_library_listing_data_to_context' ) );
			add_filter( 'timber/context', array( $this, 'add_library_listing_search_data_to_context' ) );
			add_filter( 'timber/context', array( $this, 'add_library_feeds_to_context' ) );
		}
	}

	/**
	 * Add library sidebar data to context.
	 *
	 * @param array $context the timber context array.
	 * @return array $context the timber context array with library sidebar data.
	 */
	public function add_library_sidebar_data_to_context( $context ) {
		$context = $this->add_author_data_to_context( $context );
		$context = $this->add_downloads_data_to_context( $context );

		$display_press_details = get_field( 'display_press_details' );
		if ( $display_press_details ) {
			$press_details                             = get_field( 'press_enquiries_details', 'option' );
			$context['library_sidebar']['press_tel']   = esc_html( $press_details['phone_number'] );
			$context['library_sidebar']['press_email'] = esc_html( $press_details['email'] );
			$context['library_sidebar']['press_intro'] = esc_html( $press_details['name'] );
		}

		$library_type = get_field( 'library_type' );
		if ( $library_type ) {
			$context['library_sidebar']['cats'] = array(
				array(
					'title' => esc_html( $library_type->name ),
					'link'  => $this->get_term_filtered_url( $library_type ),
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

		$context['library_sidebar']['tags'] = $tags;

		return $context;
	}

	/**
	 * Add author data to context, within the library_sidebar element.
	 *
	 * @param array $context the timber context array.
	 * @return array $context the timber context array with author data.
	 */
	private function add_author_data_to_context( $context ) {
		$authors = get_field( 'authors' );

		if ( $authors ) {
			foreach ( $authors as $key => $author ) {
				$context['library_sidebar']['accordion_items'][] = $this->get_author_item_data( $author['author'] );
			}
		}

		return $context;
	}

	/**
	 * Get author info for a single author item.
	 *
	 * @param array $author_item acf repeater data for authors field.
	 * @return array $output formatted data for a single author as expected by twig.
	 */
	private function get_author_item_data( $author_item ) {
		$output = array();
		if ( 'manual' === $author_item['type'] ) {
			$output['title'] = esc_html( $author_item['name'] );
		} else {
			$author_profile_id = $author_item['profile'];
			$responsive_images = new FB_WP_HC2_Responsive_Images();
			if ( $author_profile_id ) {
				$image_id             = get_field( 'listing_image', $author_profile_id );
				$output['image_data'] = $responsive_images->get_image_data( $image_id, 'card_image' );
				$output['title']      = esc_html( get_the_title( $author_profile_id ) );
				$output['role']       = get_field( 'role', $author_profile_id );

				$contact_channels = get_field( 'contact_channels', $author_profile_id );
				if ( $contact_channels['email'] ) {
					$output['author_info']['email']['title']    = esc_html( $contact_channels['email'] );
					$output['author_info']['email']['selector'] = 'mail';
					$output['author_info']['email']['link']     = 'mailto:' . esc_html( $contact_channels['email'] );
				}

				if ( $contact_channels['twitter'] ) {
					$output['author_info']['twitter']['title']    = 'Twitter';
					$output['author_info']['twitter']['selector'] = 'twitter';
					$output['author_info']['twitter']['link']     = esc_url( $contact_channels['twitter'] );
				}

				if ( $contact_channels['facebook'] ) {
					$output['author_info']['facebook']['title']    = 'Facebook';
					$output['author_info']['facebook']['selector'] = 'facebook';
					$output['author_info']['facebook']['link']     = esc_url( $contact_channels['facebook'] );
				}

				if ( $contact_channels['linkedin'] ) {
					$output['author_info']['linkedin']['title']    = 'Linkedin';
					$output['author_info']['linkedin']['selector'] = 'linkedin';
					$output['author_info']['linkedin']['link']     = esc_url( $contact_channels['linkedin'] );
				}

				if ( $contact_channels['website'] ) {
					$output['author_info']['website']['title']    = 'Website';
					$output['author_info']['website']['selector'] = 'link';
					$output['author_info']['website']['link']     = esc_url( $contact_channels['website'] );
				}
			}
		}
		return $output;
	}

	/**
	 * Add downloads data to context, within the library_sidebar element.
	 *
	 * @param array $context the timber context array.
	 * @return array $context the timber context array with downloads data.
	 */
	private function add_downloads_data_to_context( $context ) {
		$downloads = get_field( 'files' );

		if ( $downloads ) {
			foreach ( $downloads as $key => $download ) {
				$context['library_sidebar']['postdownloads'][] = $this->get_download_item_data( $download );
			}
		}

		return $context;
	}

	/**
	 * Get file info for a single download item.
	 *
	 * @param array $download_item acf repeater data for file field.
	 * @return array $output formatted data for a single download as expected by twig.
	 */
	private function get_download_item_data( $download_item ) {
		$output = array();

		$full_filename  = $download_item['file']['filename'];
		$filename_parts = explode( '.', $full_filename );

		$file_extension = false;
		if ( is_array( $filename_parts ) ) {
			$file_extensions_supported_by_fe_svg = array(
				'doc',
				'pdf',
				'ppt',
				'xls',
				'zip',
			);
			$raw_file_extension                  = end( $filename_parts );
			if ( in_array( $raw_file_extension, $file_extensions_supported_by_fe_svg, true ) ) {
				$file_extension = $raw_file_extension;
			}
		}

		$output['type']  = $file_extension ? esc_html( $file_extension ) : false;
		$output['title'] = $download_item['file_label'] ? esc_html( $download_item['file_label'] ) : esc_html( $download_item['file']['filename'] );
		$output['link']  = esc_url( $download_item['file']['url'] );
		return $output;
	}

	/**
	 * Add library listing data to context.
	 *
	 * @param array $context the timber context array.
	 * @return array $context the timber context array with library listing data.
	 */
	public function add_library_listing_data_to_context( $context ) {
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
	 * Add data for search/filters.
	 *
	 * @param array $context the timber context array.
	 * @return array $context the timber context array with search/filters data.
	 */
	public function add_library_listing_search_data_to_context( $context ) {
		$context['search']['library_types']  = $this->get_taxonomy_terms( 'library-type' );
		$context['search']['library_topics'] = $this->get_taxonomy_terms( 'topic' );
		$context['search']['library_themes'] = $this->get_taxonomy_terms( 'themes' );

		if ( $this->is_search() ) {
			$library_type                         = $this->get_query_string_value( 'library-type' );
			$context['selected']['library_type']  = $library_type;
			$library_topic                        = $this->get_query_string_value( 'library-topic' );
			$context['selected']['library_topic'] = $library_topic;
			$library_theme                        = $this->get_query_string_value( 'library-theme' );
			$context['selected']['library_theme'] = $library_theme;
		}

		return $context;
	}

	/**
	 * Add library feed data to context.
	 *
	 * @param array $context the timber context array.
	 * @return array $context the timber context array with library feed data.
	 */
	public function add_library_feeds_to_context( $context ) {
		$featured_posts     = $this->get_featured_library_posts();
		$featured_posts_ids = array_column( $featured_posts, '_id' );
		$posts              = $this->get_library_posts( $featured_posts_ids );

		$context['featured_cards'] = $featured_posts;

		$context['library_feed'] = $posts['posts'];
		$context['pagination']   = $posts['pagination'];
		return $context;
	}

	/**
	 * Get featured library posts.
	 *
	 * @param int $posts_per_page the max number of featured posts to get.
	 * @return array $posts the featured library posts.
	 */
	private function get_featured_library_posts( $posts_per_page = 3 ) {
		// No featured posts when filtering.
		if ( $this->is_search() ) {
			return array();
		}

		$args    = array(
			'post_type'      => 'library',
			'posts_per_page' => $posts_per_page,
			'meta_query'     => array(
				array(
					'key'   => 'featured_item',
					'value' => 1,
				),
			),
		);
		$results = new WP_Query( $args );
		if ( is_array( $results->posts ) && count( $results->posts ) > 0 ) {
			return $this->twiggify_library_posts( $results->posts );
		} else {
			return array();
		}
	}

	/**
	 * Get library posts.
	 *
	 * @param array $exclude_posts ID of all posts to exclude from query.
	 * @return array $posts the library posts.
	 */
	private function get_library_posts( $exclude_posts ) {
		$paged = get_query_var( 'paged', 1 );
		$args  = array(
			'post_type'    => 'library',
			'post__not_in' => $exclude_posts,
			'paged'        => $paged,
		);

		if ( $this->is_search() ) {
			$tax_query_parts = array();
			$library_type    = $this->get_query_string_value( 'library-type' );
			if ( $library_type ) {
				$tax_query_parts[] = array(
					'taxonomy' => 'library-type',
					'terms'    => $library_type,
				);
			}
			$library_topic = $this->get_query_string_value( 'library-topic' );
			if ( $library_topic ) {
				$tax_query_parts[] = array(
					'taxonomy' => 'topic',
					'terms'    => $library_topic,
				);
			}
			$library_theme = $this->get_query_string_value( 'library-theme' );
			if ( $library_theme ) {
				$tax_query_parts[] = array(
					'taxonomy' => 'themes',
					'terms'    => $library_theme,
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
				'posts'      => $this->twiggify_library_posts( $results->posts ),
				'pagination' => new Timber\Pagination( array(), $results ),
			);
		} else {
			return array();
		}
	}

	/**
	 * Twiggify library posts.
	 *
	 * @param array $library_posts library post objects.
	 * @return array $cards library post data formatted for twig.
	 */
	private function twiggify_library_posts( $library_posts ) {
		$cards             = array();
		$responsive_images = new FB_WP_HC2_Responsive_Images();
		if ( ! empty( $library_posts ) ) {
			foreach ( $library_posts as $key => $post ) {
				$cards[ $key ]['_id']        = $post->ID;
				$cards[ $key ]['path']       = esc_url( get_the_permalink( $post ) );
				$cards[ $key ]['image_data'] = $responsive_images->get_image_data( get_field( 'listing_image', $post->ID ), 'card_image' );
				$cards[ $key ]['label']      = $this->get_library_type_name( $post );
				$cards[ $key ]['label_path'] = $this->get_term_filtered_url( get_field( 'library_type', $post->ID ) );
				$cards[ $key ]['title']      = esc_html( get_the_title( $post ) );
				$cards[ $key ]['date']       = get_the_date( 'U', $post->ID );
				$cards[ $key ]['tags']       = $this->get_library_tags( $post );
				$cards[ $key ]['summary']    = nl2br( wp_strip_all_tags( get_field( 'listing_summary', $post->ID ) ) );
				$featured                    = get_field( 'featured_item', $post->ID );
				if ( $featured ) {
					$cards[ $key ]['featured'] = true;
				}
			}
		}
		return $cards;
	}

	/**
	 * Get tags for a library post.
	 *
	 * @param object $post library post object.
	 * @return array $tags tags data for twig.
	 */
	private function get_library_tags( $post ) {
		$tags           = array();
		$library_themes = get_field( 'themes', $post->ID );
		$library_topics = get_field( 'topics', $post->ID );
		$i              = 0;

		if ( $library_themes ) {
			foreach ( $library_themes as $key => $theme ) {
				$tags[ $i ]['path']  = $this->get_term_filtered_url( $theme );
				$tags[ $i ]['title'] = $theme->name;
				++$i;
			}
		}

		if ( $library_topics ) {
			foreach ( $library_topics as $key => $topic ) {
				$tags[ $i ]['path']  = $this->get_term_filtered_url( $topic );
				$tags[ $i ]['title'] = $topic->name;
				++$i;
			}
		}
		return $tags;
	}


	/**
	 * Get the library type name.
	 *
	 * @param object $post library post object.
	 * @return string $term_name the library type name.
	 */
	private function get_library_type_name( $post ) {
		$library_type = get_field( 'library_type', $post->ID );
		if ( $library_type && isset( $library_type->name ) ) {
			$library_type_name = $library_type->name;
			return $library_type_name;
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
		$base_url = get_the_permalink( LIBRARY_ITEMS_LISTING_PAGE );
		switch ( $term->taxonomy ) {
			case 'topic':
				$taxonomy_query_string_name = 'library-topic';
				break;

			case 'themes':
				$taxonomy_query_string_name = 'library-theme';
				break;

			default:
				$taxonomy_query_string_name = $term->taxonomy;
		}
		$term_filtered_url = $base_url . '?' . $taxonomy_query_string_name . '=' . $term->term_id . '&search=1';
		return $term_filtered_url;
	}
}
