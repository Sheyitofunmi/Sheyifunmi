<?php
/**
 * Plugin Name: Homeycom3 theme class
 * Plugin URI: http://fatbeehive.com
 * Description: Class for theme customisations
 * Author: Fat Beehive
 * Author URI: http://fatbeehive.com
 * Version: 1.0
 *
 * @package FB WP HC3 Theme Settings
 */

/**
 * Class Honeycom3
 */
class Honeycom3 {
	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_honeycom3_editor_js' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_front_end_js_and_styles' ) );
		add_filter( 'timber/context', array( $this, 'add_homepage_data_to_context' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_honeycom3_editor_css' ) );
		add_filter( 'timber/context', array( $this, 'add_profile_socials_data_to_context' ) );
		add_filter( 'timber/context', array( $this, 'add_page_sidebar_data_to_context' ) );
	}

	/**
	 * Enqueue theme JS.
	 */
	public function enqueue_honeycom3_editor_js() {
		$src  = get_stylesheet_directory_uri() . '/assets/js/honeycom3.js';
		$deps = array( 'wp-blocks', 'wp-dom-ready', 'wp-edit-post' );
		wp_enqueue_script( 'honeycom3_editor_js', $src, $deps, '1.0.0', true );
	}

	/**
	 * Enqueue admin CSS.
	 */
	public function enqueue_honeycom3_editor_css() {
		$src = get_stylesheet_directory_uri() . '/assets/css/wordpress-editor.css';
		wp_enqueue_style( 'honeycom3_editor_css', $src, array(), '1.0.0', 'all' );
	}

	/**
	 * Enqueue HC3 JS and CSS.
	 */
	public function enqueue_front_end_js_and_styles() {
		// Deregister core jQuery in favour of HC3 jQuery.
		wp_deregister_script( 'jquery' );
		wp_register_script( 'jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js', array(), '3.6.3', true );
		wp_enqueue_script( 'jquery' );

		wp_enqueue_style( 'honeycom3', get_stylesheet_directory_uri() . '/assets/css/style.css', array(), '1.0', 'screen' );
		wp_enqueue_style( 'honeycom3_print', get_stylesheet_directory_uri() . '/assets/css/print.css', array(), '1.0', 'print' );

		wp_enqueue_script( 'honeycom3_min_js', get_stylesheet_directory_uri() . '/assets/js/core.min.js', array( 'jquery' ), '1.0', true );
	}

	/**
	 * Add homepage data to context.
	 *
	 * @param array $context the timber context array.
	 * @return array $context the timber context array with homepage data.
	 */
	public function add_homepage_data_to_context( $context ) {
		if ( is_front_page() ) {
			$context['signpost_cards']       = $this->get_signpost_cards();
			$context['impact_overview']      = $this->get_homepage_impact_overview();
			$context['home_ctas']            = $this->get_homepage_ctas();
			$context['home_stats']           = $this->get_homepage_stats();
			$context['home_posts_feed_tabs'] = $this->get_homepage_posts_feed_tabs();
		}
		return $context;
	}

	/**
	 * Add profiles socials data to context.
	 *
	 * @param array $context the timber context array.
	 * @return array $context the timber context array with socials data.
	 */
	public function add_profile_socials_data_to_context( $context ) {
		if ( is_page_template( 'page-profiles.php' ) ) {
			$context['hero_socials'] = $this->get_profile_socials();
		}
		return $context;
	}

	/**
	 * Add page sidebar data to context.
	 *
	 * @param array $context the timber context array.
	 * @return array $context the timber context array with page sidebar data.
	 */
	public function add_page_sidebar_data_to_context( $context ) {
		if ( is_page() ) {
			$headlines = get_field( 'headlines' );
			if ( $headlines ) {
				foreach ( $headlines as $key => $headline ) {
					$context['page_sidebar']['headlines'][ $key ]['title'] = esc_html( $headline['title'] );
				}
			}
		}
		return $context;
	}


	/**
	 * Get profile socials data.
	 *
	 * @return array $socials socials data formatted for twig.
	 */
	private function get_profile_socials() {
		$contact_channels = get_field( 'contact_channels' );
		$socials          = array();
		if ( isset( $contact_channels['email'] ) && $contact_channels['email'] ) {
			$socials['email']['selector'] = 'email';
			$socials['email']['path']     = 'mailto:' . esc_html( $contact_channels['email'] );
		}
		if ( isset( $contact_channels['twitter'] ) && $contact_channels['twitter'] ) {
			$socials['twitter']['selector'] = 'twitter';
			$socials['twitter']['path']     = esc_url( $contact_channels['twitter'] );
		}
		if ( isset( $contact_channels['facebook'] ) && $contact_channels['facebook'] ) {
			$socials['facebook']['selector'] = 'facebook';
			$socials['facebook']['path']     = esc_url( $contact_channels['facebook'] );
		}
		if ( isset( $contact_channels['linkedin'] ) && $contact_channels['linkedin'] ) {
			$socials['linkedin']['selector'] = 'linkedin';
			$socials['linkedin']['path']     = esc_url( $contact_channels['linkedin'] );
		}
		if ( isset( $contact_channels['website'] ) && $contact_channels['website'] ) {
			$socials['website']['selector'] = 'link';
			$socials['website']['path']     = esc_url( $contact_channels['website'] );
		}
		return $socials;
	}


	/**
	 * Get signposts data.
	 *
	 * @return array $cards signposts data formatted for twig.
	 */
	private function get_signpost_cards() {
		$component = get_field( 'signposts' );
		$cards     = array();
		if ( $component['signposts'] ) {
			foreach ( $component['signposts'] as $key => $item ) {
				$cards[ $key ]['title']   = esc_html( $item['title'] );
				$cards[ $key ]['summary'] = nl2br( esc_html( $item['summary'] ) );
				$cards[ $key ]['path']    = esc_url( $item['link']['url'] );
			}
		}
		return $cards;
	}

	/**
	 * Get impact overview data.
	 *
	 * @return array $impact impact data formatted for twig.
	 */
	private function get_homepage_impact_overview() {
		$component = get_field( 'impact_overview' );
		$impact    = array();
		if ( $component['impact_items'] ) {
			$responsive_images = new FB_WP_HC2_Responsive_Images();
			foreach ( $component['impact_items'] as $key => $item ) {
				switch ( $item['type'] ) {
					case 'image':
						$impact[ $key ]['image_data'] = $responsive_images->get_image_data( $item['image'], 'card_image' );
						break;

					case 'blockquote':
						$impact[ $key ]['blockquote']  = nl2br( esc_html( $item['blockquote']['blockquote'] ) );
						$impact[ $key ]['attribution'] = esc_html( $item['blockquote']['attribution'] );
						break;

					case 'cta':
						$impact[ $key ]['cta_title']      = esc_html( $item['call_to_action']['title'] );
						$impact[ $key ]['cta_summary']    = nl2br( esc_html( $item['call_to_action']['summary'] ) );
						$impact[ $key ]['cta_image_data'] = $responsive_images->get_image_data( $item['call_to_action']['image'], 'card_image' );
						$impact[ $key ]['cta_button']     = esc_html( $item['call_to_action']['link']['title'] );
						$impact[ $key ]['cta_path']       = esc_url( $item['call_to_action']['link']['url'] );
						break;

					case 'stat':
						$post_id = $item['statistic']['statistic_post'];
						if ( $post_id ) {
							$impact[ $key ]['stat_value_prefix'] = esc_html( get_field( 'pre_stat_symbol', $post_id ) );
							$impact[ $key ]['stat_value']        = esc_html( get_field( 'stat_number', $post_id ) );
							$impact[ $key ]['stat_value_suffix'] = esc_html( get_field( 'post_stat_symbol', $post_id ) );
							$impact[ $key ]['stat_title']        = esc_html( get_field( 'title', $post_id ) );
							$impact[ $key ]['stat_summary']      = nl2br( esc_html( get_field( 'summary', $post_id ) ) );
						}
						break;
				}
			}
		}
		return $impact;
	}

	/**
	 * Get homepage cta data.
	 *
	 * @return array $cards cta data formatted for twig.
	 */
	private function get_homepage_ctas() {
		$component = get_field( 'calls_to_action' );
		$cards     = array();
		if ( $component['call_to_action'] ) {
			$responsive_images = new FB_WP_HC2_Responsive_Images();
			foreach ( $component['call_to_action'] as $key => $item ) {
				$cards[ $key ]['title']      = esc_html( $item['title'] );
				$cards[ $key ]['summary']    = nl2br( esc_html( $item['summary'] ) );
				$cards[ $key ]['image_data'] = $responsive_images->get_image_data( $item['image'], 'card_image' );
				$cards[ $key ]['path']       = esc_url( $item['link']['url'] );
			}
		}
		return $cards;
	}

	/**
	 * Get homepage stats data.
	 *
	 * @return array $statistics statistics data formatted for twig.
	 */
	private function get_homepage_stats() {
		$component  = get_field( 'statistics' );
		$statistics = array();
		if ( $component['statistics'] ) {
			foreach ( $component['statistics'] as $key => $item ) {
				$statistic_post_id                  = $item['statistic'];
				$statistics[ $key ]['title']        = esc_html( get_field( 'title', $statistic_post_id ) );
				$statistics[ $key ]['summary']      = nl2br( esc_html( get_field( 'summary', $statistic_post_id ) ) );
				$statistics[ $key ]['value']        = esc_html( get_field( 'stat_number', $statistic_post_id ) );
				$statistics[ $key ]['value_prefix'] = esc_html( get_field( 'pre_stat_symbol', $statistic_post_id ) );
				$statistics[ $key ]['value_suffix'] = esc_html( get_field( 'post_stat_symbol', $statistic_post_id ) );
				$source_type                        = get_field( 'source_type', $statistic_post_id );
				if ( 'text' === $source_type ) {
					$statistics[ $key ]['source'] = esc_html( get_field( 'source', $statistic_post_id ) );
				} else {
					$source_link = get_field( 'source_link', $statistic_post_id );
					if ( $source_link ) {
						$statistics[ $key ]['source_link']['path']  = esc_url( $source_link['url'] );
						$statistics[ $key ]['source_link']['title'] = esc_html( $source_link['title'] );
					}
				}
			}
		}
		return $statistics;
	}

	/**
	 * Get homepage posts feed data.
	 *
	 * @return array $tabs posts feed data formatted for twig.
	 */
	private function get_homepage_posts_feed_tabs() {
		$component         = get_field( 'posts_feed' );
		$responsive_images = new FB_WP_HC2_Responsive_Images();
		$tabs              = array();
		if ( $component['updates_feed'] ) {
			$updates = $this->get_homepage_updates();
			if ( $updates ) {
				$tabs['updates']['title']     = esc_html( $component['updates_feed_title'] );
				$tabs['updates']['link']      = esc_html( $component['updates_feed_link']['title'] );
				$tabs['updates']['link_path'] = esc_url( $component['updates_feed_link']['url'] );
				foreach ( $updates as $key => $update ) {
					$tabs['updates']['cards'][ $key ]['path']       = esc_html( get_the_permalink( $update ) );
					$tabs['updates']['cards'][ $key ]['image_data'] = $responsive_images->get_image_data( get_field( 'listing_image', $update->ID ), 'card_image' );
					$tabs['updates']['cards'][ $key ]['title']      = get_the_title( $update );
					$summary                                        = get_field( 'listing_summary', $update->ID );
					$tabs['updates']['cards'][ $key ]['summary']    = nl2br( esc_html( $summary ) );
					$tabs['updates']['cards'][ $key ]['tags']       = $this->get_post_tags( $update );
				}
			}
		}
		if ( $component['events_feed'] ) {
			$events = $this->get_homepage_events();
			if ( $events ) {
				$tabs['events']['title']     = esc_html( $component['events_feed_title'] );
				$tabs['events']['link']      = esc_html( $component['events_feed_link']['title'] );
				$tabs['events']['link_path'] = esc_url( $component['events_feed_link']['url'] );
				foreach ( $events as $key => $event ) {
					$tabs['events']['cards'][ $key ]['path']       = esc_html( get_the_permalink( $event ) );
					$tabs['events']['cards'][ $key ]['image_data'] = $responsive_images->get_image_data( get_field( 'listing_image', $event->ID ), 'card_image' );
					$tabs['events']['cards'][ $key ]['title']      = get_the_title( $event );
					$summary                                       = get_field( 'listing_summary', $event->ID );
					$tabs['events']['cards'][ $key ]['summary']    = nl2br( esc_html( $summary ) );
					$tabs['events']['cards'][ $key ]['tags']       = $this->get_post_tags( $event );
				}
			}
		}
		if ( $component['library_feed'] ) {
			$library_items = $this->get_homepage_library_items();
			if ( $library_items ) {
				$tabs['library']['title']     = esc_html( $component['library_feed_title'] );
				$tabs['library']['link']      = esc_html( $component['library_feed_link']['title'] );
				$tabs['library']['link_path'] = esc_url( $component['library_feed_link']['url'] );
				foreach ( $library_items as $key => $library_item ) {
					$tabs['library']['cards'][ $key ]['path']       = esc_html( get_the_permalink( $library_item ) );
					$tabs['library']['cards'][ $key ]['image_data'] = $responsive_images->get_image_data( get_field( 'listing_image', $library_item->ID ), 'card_image' );
					$tabs['library']['cards'][ $key ]['title']      = get_the_title( $library_item );
					$summary                                        = get_field( 'listing_summary', $library_item->ID );
					$tabs['library']['cards'][ $key ]['summary']    = nl2br( esc_html( $summary ) );
					$tabs['library']['cards'][ $key ]['tags']       = $this->get_post_tags( $library_item );
				}
			}
		}
		return $tabs;
	}

	/**
	 * Get update posts marked as featured on homepage.
	 *
	 * @param int $posts_per_page the number of posts to retrieve.
	 * @return array $posts update posts for homepage.
	 */
	private function get_homepage_updates( $posts_per_page = 2 ) {
		$args    = array(
			'post_type'      => 'update',
			'posts_per_page' => $posts_per_page,
			'meta_key'       => 'featured_on_homepage_posts_feed',
			'meta_value'     => 1,
			'orderby'        => 'date',
			'order'          => 'DESC',
		);
		$results = new WP_Query( $args );
		if ( is_array( $results->posts ) && count( $results->posts ) > 0 ) {
			return $results->posts;
		} else {
			return false;
		}
	}

	/**
	 * Get event posts marked as featured on homepage.
	 *
	 * @param int $posts_per_page the number of posts to retrieve.
	 * @return array $posts event posts for homepage.
	 */
	private function get_homepage_events( $posts_per_page = 2 ) {
		$args        = array(
			'post_type'      => 'event',
			'posts_per_page' => $posts_per_page,
			'meta_key'       => 'start_date',
			'orderby'        => 'meta_value_num',
			'order'          => 'ASC',
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'key'   => 'featured_on_homepage_posts_feed',
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
			return $results->posts;
		} else {
			return false;
		}
	}

	/**
	 * Get library posts marked as featured on homepage.
	 *
	 * @param int $posts_per_page the number of posts to retrieve.
	 * @return array $posts library posts for homepage.
	 */
	private function get_homepage_library_items( $posts_per_page = 2 ) {
		$args    = array(
			'post_type'      => 'library',
			'posts_per_page' => $posts_per_page,
			'meta_key'       => 'featured_on_homepage_posts_feed',
			'meta_value'     => 1,
			'orderby'        => 'date',
			'order'          => 'DESC',
		);
		$results = new WP_Query( $args );
		if ( is_array( $results->posts ) && count( $results->posts ) > 0 ) {
			return $results->posts;
		} else {
			return false;
		}
	}

	/**
	 * Get post tags.
	 *
	 * @param object $post the post object.
	 * @return array $tags tags for the post, formatted for twig.
	 */
	private function get_post_tags( $post ) {
		$tags = array();
		$i    = 0;
		if ( is_object( $post ) && isset( $post->post_type ) ) {
			switch ( $post->post_type ) {
				case 'update':
					$update_types = $this->get_post_tags_by_acf_field_name( 'update_type', $post->ID );
					if ( $update_types ) {
						$tags = array_merge( $tags, $update_types );
					}

					$themes = $this->get_post_tags_by_acf_field_name( 'themes', $post->ID );
					if ( $themes ) {
						$tags = array_merge( $tags, $themes );
					}

					$topics = $this->get_post_tags_by_acf_field_name( 'topics', $post->ID );
					if ( $topics ) {
						$tags = array_merge( $tags, $topics );
					}
					break;

				case 'event':
					$event_types = $this->get_post_tags_by_acf_field_name( 'event_type', $post->ID );
					if ( $event_types ) {
						$tags = array_merge( $tags, $event_types );
					}

					$event_category = $this->get_post_tags_by_acf_field_name( 'event_category', $post->ID );
					if ( $event_category ) {
						$tags = array_merge( $tags, $event_category );
					}

					$event_location = $this->get_post_tags_by_acf_field_name( 'event_location', $post->ID );
					if ( $event_location ) {
						$tags = array_merge( $tags, $event_location );
					}
					break;

				case 'library':
					$library_types = $this->get_post_tags_by_acf_field_name( 'library_type', $post->ID );
					if ( $library_types ) {
						$tags = array_merge( $tags, $library_types );
					}
					break;
			}
		}
		return $tags;
	}

	/**
	 * Get post tags data by ACF field name.
	 *
	 * @param string $field_name ACF field name associated with the taxonomy.
	 * @param int    $post_id ID of the post to get tags for.
	 * @return array $tags tags formatted for twig.
	 */
	private function get_post_tags_by_acf_field_name( $field_name, $post_id ) {
		$tags  = array();
		$terms = get_field( $field_name, $post_id );

		if ( $terms && is_array( $terms ) ) {
			// if multiselect taxonomy field.
			foreach ( $terms as $key => $term ) {
				$tags[ $key ]['path']  = '#';
				$tags[ $key ]['title'] = esc_html( $term->name );
			}
			return $tags;
		} elseif ( $terms && is_object( $terms ) ) {
			// if single select taxonomy field.
			$tags[0]['path']  = '#';
			$tags[0]['title'] = esc_html( $terms->name );
			return $tags;
		} else {
			return false;
		}
	}
}
