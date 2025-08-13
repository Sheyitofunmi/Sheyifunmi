<?php
/**
 * Plugin Name: FB WP HC3 Theme Settings
 * Plugin URI: http://fatbeehive.com
 * Description: Class for theme settings and customisations
 * Author: Fat Beehive
 * Author URI: http://fatbeehive.com
 * Version: 1.0
 *
 * @package FB WP HC3 Theme Settings
 */

/**
 * Class FB_WP_Theme_Settings
 */
class FB_WP_HC3_Theme_Settings {
	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->add_general_settings_page();
		add_action( 'acf/init', array( $this, 'set_google_maps_api_key' ) );
		add_filter( 'acf/settings/save_json', array( $this, 'acf_json_save_point' ) );
		add_filter( 'acf/settings/load_json', array( $this, 'acf_json_load_point' ) );
		add_filter( 'publishpressfuture_expiration_actions', array( $this, 'filter_expiration_actions' ), 10, 2 );
		add_action( 'acf/validate_value/key=field_64db9a0eb3144', array( $this, 'validate_phone_number' ), 10, 4 );
		add_action( 'acf/validate_value/key=field_64dbb1a78f07d', array( $this, 'validate_phone_number' ), 10, 4 );
		add_action( 'acf/validate_value/key=field_64dbb291a04ca', array( $this, 'validate_phone_number' ), 10, 4 );
		add_action( 'acf/validate_value/key=field_64ddf84409714', array( $this, 'validate_phone_number' ), 10, 4 );
		add_action( 'timber/context', array( $this, 'add_assets_path_to_twig_context' ) );
		add_filter( 'tiny_mce_before_init', array( $this, 'customise_tinymce' ), 10, 2 );
		add_filter( 'body_class', array( $this, 'customise_body_classes' ) );
		add_filter( 'timber/context', array( $this, 'add_options_to_context' ) );
		add_filter( 'timber/context', array( $this, 'add_header_data_to_context' ) );
		add_filter( 'timber/context', array( $this, 'add_socials_data_to_context' ) );
		add_filter( 'timber/context', array( $this, 'add_s_query_to_timber_context' ) );
		add_filter( 'timber/context', array( $this, 'add_search_form_data_to_context' ) );
		add_filter( 'timber/context', array( $this, 'add_social_media_sharing_data_to_context' ) );
		add_filter( 'timber/context', array( $this, 'add_hero_buttons_data_to_context' ) );
		add_filter( 'timber/context', array( $this, 'add_alert_banner_data_to_context' ) );
		add_action( 'admin_menu', array( $this, 'remove_default_post_type_from_admin_menu' ) );
		add_action( 'admin_menu', array( $this, 'remove_relevanssi_menu_items_for_non_admins' ), 999 );
		add_action( 'admin_bar_menu', array( $this, 'remove_default_post_type_from_admin_bar_menu' ), 999 );
		add_action( 'wp_dashboard_setup', array( $this, 'remove_dashboard_draft_widget' ), 999 );
		add_action( 'wp_dashboard_setup', array( $this, 'remove_wordfence_dashboard_widget' ) );
		add_filter( 'redirection_role', array( $this, 'allow_editor_access_to_redirect_plugin' ), 10, 1 );
		add_action( 'wp_enqueue_scripts', array( $this, 'remove_wp_block_library_css' ), 100 );
		add_filter( 'the_seo_framework_metabox_context', array( $this, 'move_seo_framework_fields_to_sidebar' ) );
	}

	/**
	 * Update the save path for acf json.
	 *
	 * @param string $path the acf json path.
	 * @return string $path the updated acf json path.
	 */
	public function acf_json_save_point( $path ) {
		$path = get_stylesheet_directory() . '/../../../../private/acf-json';
		return $path;
	}

	/**
	 * Update the load path for acf json.
	 *
	 * @param array $paths the acf json paths.
	 * @return array $path the updated acf json paths.
	 */
	public function acf_json_load_point( $paths ) {
		unset( $paths[0] );
		$paths[] = get_stylesheet_directory() . '/../../../../private/acf-json';
		return $paths;
	}

	/**
	 * Remove the default post type from the admin menu.
	 */
	public function remove_default_post_type_from_admin_menu() {
		remove_menu_page( 'edit.php' );
	}

	/**
	 * Remove add new post from the wp admin bar menu.
	 *
	 * @param object $wp_admin_bar the admin bar object.
	 */
	public function remove_default_post_type_from_admin_bar_menu( $wp_admin_bar ) {
		$wp_admin_bar->remove_node( 'new-post' );
	}

	/**
	 * Remove the draft widget from the dashboard.
	 */
	public function remove_dashboard_draft_widget() {
		$screen = get_current_screen();
		if ( ! $screen ) {
			return;
		}
		remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
	}

	/**
	 * Remove the wordfence widget from the dashboard.
	 */
	public function remove_wordfence_dashboard_widget() {
		$screen = get_current_screen();
		if ( ! $screen ) {
			return;
		}
		remove_meta_box( 'wordfence_activity_report_widget', 'dashboard', 'normal' );
	}

	/**
	 * Remove relevanssi sub menus from dashboard for non admins.
	 */
	public function remove_relevanssi_menu_items_for_non_admins() {
		if ( ! current_user_can( 'install_plugins' ) ) {
			remove_submenu_page( 'index.php', 'relevanssi-premium/relevanssi.php' );
			remove_submenu_page( 'index.php', 'relevanssi_admin_search' );
		}
	}

	/**
	 * Add ACF options page for general settings.
	 */
	public function add_general_settings_page() {
		if ( function_exists( 'acf_add_options_page' ) ) {
			acf_add_options_page(
				array(
					'page_title' => 'General Settings',
					'menu_title' => 'General Settings',
					'menu_slug'  => 'general-settings',
					'capability' => 'edit_others_posts',
					'redirect'   => false,
				)
			);
		}
	}

	/**
	 * Add acf options data to context.
	 *
	 * @param array $context the timber context array.
	 * @return array $context the timber context array with options data.
	 */
	public function add_options_to_context( $context ) {
		$context['options'] = get_fields( 'options' );
		return $context;
	}

	/**
	 * Add socials data to context.
	 *
	 * @param array $context the timber context array.
	 * @return array $context the timber context array with the socials data.
	 */
	public function add_socials_data_to_context( $context ) {
		$socials = get_field( 'social_profiles', 'option' );

		$socials_data = array();

		if ( $socials ) {
			foreach ( $socials as $key => $social ) {
				if ( ! isset( $social['url'] ) ) {
					continue;
				}
				$socials_data[ $key ]['selector'] = esc_html( $key );
				$socials_data[ $key ]['path']     = esc_url( $social['url'] );
				$socials_data[ $key ]['target']   = esc_attr( $social['target'] );
			}
		}

		$context['socials'] = $socials_data;

		return $context;
	}

	/**
	 * Add header data to context.
	 *
	 * @param array $context the timber context array.
	 * @return array $context the timber context array with header data.
	 */
	public function add_header_data_to_context( $context ) {
		$primary_cta   = get_field( 'primary_call_to_action', 'option' );
		$secondary_cta = get_field( 'secondary_call_to_action', 'option' );

		$buttons = array();

		if ( $primary_cta ) {
			$buttons[0]['text']   = esc_html( $primary_cta['title'] );
			$buttons[0]['url']    = esc_url( $primary_cta['url'] );
			$buttons[0]['target'] = esc_attr( $primary_cta['target'] );
		}

		if ( $secondary_cta ) {
			$buttons[1]['text']   = esc_html( $secondary_cta['title'] );
			$buttons[1]['url']    = esc_url( $secondary_cta['url'] );
			$buttons[1]['target'] = esc_attr( $secondary_cta['target'] );
		}
		$context['buttons'] = $buttons;
		return $context;
	}

	/**
	 * Add hero buttons data to context.
	 *
	 * @param array $context the timber context array.
	 * @return array $context the timber context array with hero buttons data.
	 */
	public function add_hero_buttons_data_to_context( $context ) {
		$hero_section = get_field( 'hero_section' );
		if ( isset( $hero_section['use_a_donate_block'] ) && $hero_section['use_a_donate_block'] ) {
			if ( $hero_section['single_donation_button'] ) {
				$context['hero_buttons'][0]['title']  = esc_html( $hero_section['single_donation_button']['title'] );
				$context['hero_buttons'][0]['path']   = esc_url( $hero_section['single_donation_button']['url'] );
				$context['hero_buttons'][0]['target'] = esc_attr( $hero_section['single_donation_button']['target'] );
			}
			if ( isset( $hero_section['regular_donation_button'] ) && $hero_section['regular_donation_button'] ) {
				$context['hero_buttons'][1]['title']  = esc_html( $hero_section['regular_donation_button']['title'] );
				$context['hero_buttons'][1]['path']   = esc_url( $hero_section['regular_donation_button']['url'] );
				$context['hero_buttons'][1]['target'] = esc_attr( $hero_section['regular_donation_button']['target'] );
			}
		}

		return $context;
	}

	/**
	 * Add alert banner data to context.
	 *
	 * @param array $context the timber context array.
	 * @return array $context the timber context array with alert banner data.
	 */
	public function add_alert_banner_data_to_context( $context ) {
		$is_enabled     = get_field( 'enable_alert_banner', 'option' );
		$is_scheduled   = get_field( 'schedule_the_alert_banner', 'option' );
		$title          = get_field( 'alert_banner_title', 'option' );
		$message        = get_field( 'alert_banner_message', 'option' );
		$excluded_pages = get_field( 'exclude_alert_banner_from_the_following_pages', 'option' );
		$link           = get_field( 'alert_banner_link', 'option' );
		$start_datetime = get_field( 'alert_banner_start_date_and_time', 'option' );
		$end_datetime   = get_field( 'alert_banner_end_date_and_time', 'option' );
		$current_time   = current_time( 'Y-m-d H:i:s' );
		$post_id        = get_queried_object_id();

		$context['alert_copy']          = nl2br( esc_html( $message ) );
		$context['alert_title']         = esc_html( $title );
		$context['alert_link_text']     = ! empty( $link['title'] ) && ! empty( $link['url'] ) ? esc_html( $link['title'] ) : false;
		$context['alert_link_path']     = ! empty( $link['title'] ) && ! empty( $link['url'] ) ? esc_url( $link['url'] ) : false;
		$context['alert_link_external'] = '_blank' === $link['target'];

		if ( is_array( $excluded_pages ) && in_array( $post_id, $excluded_pages ) ) {
			$context['alert'] = false;
		} elseif ( $is_enabled && ! $is_scheduled ) {
			$context['alert'] = true;
		} elseif ( $is_enabled && $is_scheduled && $current_time <= $end_datetime && $current_time >= $start_datetime ) {
			$context['alert'] = true;
		} else {
			$context['alert'] = false;
		}
		return $context;
	}

	/**
	 * Limit the actions made available by post expirator.
	 *
	 * @param array  $actions actions available.
	 * @param string $post_type the post type.
	 * @return array $actions the filtered list of actions.
	 */
	public function filter_expiration_actions( $actions, $post_type ) {
		$actions_to_disable = array(
			'stick',
			'unstick',
			'category',
			'category-add',
			'category-remove',
		);
		foreach ( $actions_to_disable as $action ) {
			unset( $actions[ $action ] );
		}
		return $actions;
	}

	/**
	 * Basic phone number validation, allowing some characters.
	 *
	 * @param mixed $valid Whether or not the value is valid.
	 * @param mixed $value The field value.
	 * @param array $field The field array containing all settings.
	 * @param array $input_name The field DOM element name attribute.
	 *
	 * @return mixed $valid Whether or not the value is valid.
	 */
	public function validate_phone_number( $valid, $value, $field, $input_name ) {
		if ( ! empty( $value ) ) {

			$allowed_characters = array(
				'+',
				'-',
				'(',
				')',
			);

			$telephone = str_replace( $allowed_characters, '', $value );
			if ( ! is_numeric( $telephone ) ) {
				return __( 'Please enter a valid telephone number', 'hc3' );
			}
		}
		return $valid;
	}

	/**
	 * Add the path to the assets directory to the context.
	 *
	 * @param array $context twig context variable.
	 *
	 * @return array $context twig context variable with assets_dir defined.
	 */
	public function add_assets_path_to_twig_context( $context ) {
		$context['assets_dir'] = get_template_directory_uri() . '/assets';
		return $context;
	}

	/**
	 * Customise tinymce buttons.
	 *
	 * @param array $init_array tinymce initarray.
	 * @param int   $editor_id tinymce initarray.
	 * @return int $editor_id editor id.
	 */
	public function customise_tinymce( $init_array, $editor_id ) {
		// Add block format elements you want to show in dropdown.
		$init_array['block_formats'] = 'Paragraph=p;Heading 2=h2;Heading 3=h3;Heading 4=h4;Heading 5=h5;Heading 6=h6';

		// Override style_formats array. Add button style.
		$style_formats = array(
			array(
				'title'    => 'Button',
				'selector' => 'a',
				'classes'  => 'button',
				'wrapper'  => false,
			),
		);
		// Insert the array, JSON ENCODED, into 'style_formats'.
		$init_array['style_formats'] = wp_json_encode( $style_formats );
		return $init_array;
	}

	/**
	 * Customise body classes.
	 *
	 * @param array $classes the $classes array from WP.
	 * @return array $classes the customised $classes array.
	 */
	public function customise_body_classes( $classes ) {
		// Remove 'search' body class. Does not play nice with HC3 FE.
		if ( is_search() && in_array( 'search', $classes ) ) {
			unset( $classes[ array_search( 'search', $classes ) ] );
		}

		$hero_section = get_field( 'hero_section' );
		if ( isset( $hero_section['use_a_donate_block'] ) ) {
			$use_donate_block = $hero_section['use_a_donate_block'];
			if ( $use_donate_block ) {
				$classes[] = 'donate-hero';
			}
		}
		if ( isset( $hero_section['enable_impact_mode'] ) ) {
			$impact_mode = $hero_section['enable_impact_mode'];
			if ( $impact_mode ) {
				$classes[] = 'impact-template';
			}
		}

		return $classes;
	}

	/**
	 * Adds the search query to the timber context.
	 *
	 * @param array $data the timber context array.
	 * @return array $data the timber context array with the search query data.
	 */
	public function add_s_query_to_timber_context( $data ) {
		$data['s_keyword'] = get_query_var( 's' );
		return $data;
	}

	/**
	 * Adds the search form data to the timber context.
	 *
	 * @param array $data the timber context array.
	 * @return array $data the timber context array with the search form data.
	 */
	public function add_search_form_data_to_context( $data ) {
		$data['search_form'] = array(
			'action'     => '/',
			'input_name' => 's',
		);
		return $data;
	}

	/**
	 * WP_Query to get search results
	 */
	public function get_search_results() {
		if ( empty( ( get_query_var( 's' ) ) ) ) {
			return false;
		}
		$args = array(
			's'     => get_query_var( 's' ),
			'paged' => get_query_var( 'paged', 1 ),
		);

		$args = apply_filters( 'fb_search_query_args', $args );

		$results = new WP_Query( $args );
		if ( function_exists( 'relevanssi_do_query' ) ) {
			relevanssi_do_query( $results );
		}
		if ( $results->posts ) {
			$output = array();

			$output['posts'] = $this->twiggify_search_results( $results->posts );

			$pagination_args = array();
			$pagination_args = apply_filters( 'fb_search_pagination_args', $pagination_args );

			$output['pagination'] = new Timber\Pagination( $pagination_args, $results );
			return $output;
		} else {
			return false;
		}
	}

	/**
	 * Build an array of posts with the elements expected by the twig template.
	 *
	 * @param array $posts the posts array from WP_Query.
	 */
	private function twiggify_search_results( $posts ) {
		$fb_wp_hc2_responsive_images = new FB_WP_HC2_Responsive_Images();
		if ( $posts ) {
			$results = array();
			foreach ( $posts as $k => $p ) {
				$results[ $k ]['path']  = get_permalink( $p->ID );
				$results[ $k ]['title'] = get_the_title( $p->ID );
				if ( 'event' === $p->post_type && get_field( 'is_recurring', $p->ID ) ) {
					$the_post_id = get_field( 'master_event_post', $current_post_id );
				} else {
					$the_post_id = $p->ID;
				}

				$results[ $k ]['summary'] = nl2br( wp_strip_all_tags( get_field( 'listing_summary', $the_post_id ) ) );
				$image_id                 = get_field( 'listing_image', $the_post_id );
				if ( $image_id ) {
					$results[ $k ]['image_data'] = $fb_wp_hc2_responsive_images->get_image_data( $image_id, 'card_image' );
				}

				$results[ $k ] = apply_filters( 'fb_search_twiggify_item', $results[ $k ], $p, $k );
			}

			return $results;
		}

		return false;
	}

	/**
	 * Adds the social sharing data to the timber context.
	 *
	 * @param array $data the timber context array.
	 * @return array $data the timber context array with the social sharing data.
	 */
	public function add_social_media_sharing_data_to_context( $data ) {
		$social_media_sharing = get_field( 'enable_social_media_sharing' );
		$post_id              = get_queried_object_id();

		// Only add social sharing data if a post single or a page.
		if ( ! is_single( $post_id ) && ! is_page( $post_id ) ) {
			return $data;
		}

		if ( isset( $social_media_sharing['enable_social_media_sharing']['enable'] ) && true === $social_media_sharing['enable_social_media_sharing']['enable'] ) {
			if ( true === $social_media_sharing['enable_social_media_sharing']['facebook'] ) {
				$data['postshares']['facebook']['selector'] = 'facebook';
				$data['postshares']['facebook']['link']     = 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( get_the_permalink( $post_id ) );
			}
			if ( true === $social_media_sharing['enable_social_media_sharing']['twitter'] ) {
				$data['postshares']['twitter']['selector'] = 'twitter';
				$data['postshares']['twitter']['link']     = 'https://twitter.com/intent/tweet?text=' . get_the_title( $post_id ) . '&url=' . rawurlencode( get_the_permalink( $post_id ) );
			}
			if ( true === $social_media_sharing['enable_social_media_sharing']['whatsapp'] ) {
				$data['postshares']['whatsapp']['selector'] = 'whatsapp';
				$data['postshares']['whatsapp']['link']     = 'whatsapp://send?text=' . rawurlencode( get_the_permalink( $post_id ) );
			}
			if ( true === $social_media_sharing['enable_social_media_sharing']['linkedin'] ) {
				$data['postshares']['linkedin']['selector'] = 'linkedin';
				$data['postshares']['linkedin']['link']     = 'https://www.linkedin.com/sharing/share-offsite/?url=' . rawurlencode( get_the_permalink( $post_id ) );
			}
			if ( true === $social_media_sharing['enable_social_media_sharing']['email'] ) {
				$data['postshares']['mail']['selector'] = 'email';
				$data['postshares']['mail']['link']     = 'mailto:?subject=' . get_the_title( $post_id ) . '&body=' . rawurlencode( get_the_permalink( $post_id ) );
			}
			if ( true === $social_media_sharing['enable_social_media_sharing']['link'] ) {
				$data['postshares']['link']['selector'] = 'link';
				$data['postshares']['link']['link']     = get_the_permalink( $post_id );
			}
		}

		return $data;
	}

	/**
	 * Set Google Maps API key for ACF.
	 */
	public function set_google_maps_api_key() {
		acf_update_setting( 'google_api_key', GMAPS_API_KEY );
	}

	/**
	 * Allow editors to set up redirects, using the redirection plugin
	 *
	 * @param string $cap capability.
	 * @return string $cap capability.
	 */
	public function allow_editor_access_to_redirect_plugin( $cap ) {
		$cap = 'publish_pages';
		return $cap;
	}

	/**
	 * Remove wp-block styling
	 */
	public function remove_wp_block_library_css() {
		wp_dequeue_style( 'wp-block-library' );
		wp_dequeue_style( 'wp-block-library-theme' );
		wp_dequeue_style( 'wc-blocks-style' );
		wp_dequeue_style( 'global-styles' );
	}

	/**
	 * Move SEO Framework metabox to the admin sidebar.
	 */
	public function move_seo_framework_fields_to_sidebar() {
		return 'side';
	}
}
