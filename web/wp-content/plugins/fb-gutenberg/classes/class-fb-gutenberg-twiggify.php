<?php
/**
 * Plugin Name: FB Gutenberg
 * Plugin URI: http://fatbeehive.com
 * Description: Class for handling data processing and formatting for twig/timber.
 * Author: Fat Beehive
 * Author URI: http://fatbeehive.com
 * Version: 1.0
 *
 * @package FB Gutenberg
 */

/**
 * Class FB_Gutenberg_Twiggify
 */
class FB_Gutenberg_Twiggify {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_kses_allowed_html', array( $this, 'allow_iframes_with_kses_post' ), 10, 2 );
	}

	/**
	 * Helper method to call return twiggified data to the block's render callback function.
	 *
	 * @param array $block  The ACF block.
	 * @param array $fields The field data for the block.
	 * @return array Twiggified data.
	 */
	public function twiggify_block_data( $block, $fields ) {
		switch ( $block['name'] ) {
			case 'acf/page-index':
				$twiggified_data = $this->twiggify_page_index_items( $fields );
				break;

			case 'acf/accordion':
				$twiggified_data = $this->twiggify_accordion_items( $fields );
				break;

			case 'acf/statistics':
				$twiggified_data = $this->twiggify_statistics_items( $fields );
				break;

			case 'acf/downloads':
				$twiggified_data = $this->twiggify_downloads_items( $fields );
				break;

			case 'acf/profiles':
				$twiggified_data = $this->twiggify_profiles_items( $fields );
				break;

			case 'acf/information-overview':
				$twiggified_data = $this->twiggify_information_overview_items( $fields );
				break;

			case 'acf/self-selection':
				$twiggified_data = $this->twiggify_self_selection_items( $fields );
				break;

			case 'acf/featured-promos':
				$twiggified_data = $this->twiggify_featured_promos_items( $fields );
				break;

			case 'acf/gallery':
				$twiggified_data = $this->twiggify_gallery_items( $fields );
				break;

			case 'acf/simple-donation':
				$twiggified_data = $this->twiggify_simple_donation_items( $fields );
				break;

			default:
				return false;
		}

		return $twiggified_data;
	}


	/**
	 * Twiggify the data for page index items.
	 *
	 * @param array $fields The field data for the block.
	 * @return array $cards Twiggified data as expected by FE template.
	 */
	private function twiggify_page_index_items( $fields ) {
		if ( ! empty( $fields['index_items'] ) ) {
			$cards = array();
			foreach ( $fields['index_items'] as $key => $index_item ) {
				$cards[ $key ]['title'] = esc_html( $index_item['title'] );
				$cards[ $key ]['id']    = esc_html( $index_item['anchor_id'] );
			}
			return $cards;
		} else {
			return false;
		}
	}

	/**
	 * Twiggify the data for accordion items.
	 *
	 * @param array $fields The field data for the block.
	 * @return array $cards Twiggified data as expected by FE template.
	 */
	private function twiggify_accordion_items( $fields ) {
		$cards = array();

		if ( ! empty( $fields['panels'] ) ) {
			foreach ( $fields['panels'] as $key => $panel_item ) {
				$cards[ $key ]['title']       = esc_html( $panel_item['title'] );
				$cards[ $key ]['description'] = wp_kses_post( $panel_item['content'] );
			}
		}

		if ( 'double' === $fields['column_layout'] && ! empty( $fields['panels_2'] ) ) {
			$cards_2 = array();
			foreach ( $fields['panels_2'] as $key => $panel_item ) {
				$cards_2[ $key ]['title']       = esc_html( $panel_item['title'] );
				$cards_2[ $key ]['description'] = wp_kses_post( $panel_item['content'] );
			}

			return array( array( 'accordions' => $cards ), array( 'accordions' => $cards_2 ) );
		} else {
			return $cards;
		}

		return false;
	}

	/**
	 * Twiggify the data for statistics items.
	 *
	 * @param array $fields The field data for the block.
	 * @return array $cards Twiggified data as expected by FE template.
	 */
	private function twiggify_statistics_items( $fields ) {
		if ( ! empty( $fields['statistics'] ) ) {
			$fb_wp_hc2_responsive_images = new FB_WP_HC2_Responsive_Images();

			$cards = array();
			foreach ( $fields['statistics'] as $key => $stat_post ) {
				$cards[ $key ]['title']        = esc_html( get_field( 'title', $stat_post->ID ) );
				$cards[ $key ]['value_prefix'] = esc_html( get_field( 'pre_stat_symbol', $stat_post->ID ) );
				$cards[ $key ]['value']        = esc_html( get_field( 'stat_number', $stat_post->ID ) );
				$cards[ $key ]['value_suffix'] = esc_html( get_field( 'post_stat_symbol', $stat_post->ID ) );
				$cards[ $key ]['summary']      = esc_html( get_field( 'summary', $stat_post->ID ) );

				$source_type = get_field( 'source_type', $stat_post->ID );
				if ( 'text' === $source_type ) {
					$cards[ $key ]['source'] = esc_html( get_field( 'source', $stat_post->ID ) );
				} else {
					$source_link = get_field( 'source_link', $stat_post->ID );
					if ( $source_link ) {
						$cards[ $key ]['source_link']['path']  = esc_url( $source_link['url'] );
						$cards[ $key ]['source_link']['title'] = esc_html( $source_link['title'] );
					}
				}

				$image_id = get_field( 'stat_image', $stat_post->ID );
				if ( $image_id ) {
					$cards[ $key ]['stat_image_data'] = $fb_wp_hc2_responsive_images->get_image_data( $image_id, 'card_image' );

				}
			}
			return $cards;
		} else {
			return false;
		}
	}

	/**
	 * Twiggify the data for downloads items.
	 *
	 * @param array $fields The field data for the block.
	 * @return array $cards Twiggified data as expected by FE template.
	 */
	private function twiggify_downloads_items( $fields ) {
		if ( ! empty( $fields['files'] ) ) {
			$cards = array();
			foreach ( $fields['files'] as $key => $file_item ) {
				$cards[ $key ]['linktext'] = esc_html( $file_item['label'] );
				$cards[ $key ]['link']     = esc_url( $file_item['file']['url'] );
				$cards[ $key ]['size']     = size_format( $file_item['file']['filesize'], 2 );
			}
			return $cards;
		} else {
			return false;
		}
	}

	/**
	 * Twiggify the data for profiles items.
	 *
	 * @param array $fields The field data for the block.
	 * @return array $cards Twiggified data as expected by FE template.
	 */
	private function twiggify_profiles_items( $fields ) {
		$responsive_images = new FB_WP_HC2_Responsive_Images();
		$cards             = array();
		//if ( 'manual' === $fields['type'] && ! empty( $fields['profiles'] ) ) { // Restore this line if client has CMS type included
		if ( ! empty( $fields['profiles'] ) ) {	
			foreach ( $fields['profiles'] as $key => $item ) {
				$cards[ $key ]['image_data'] = $responsive_images->get_image_data( $item['image'], 'portrait_image' );
				$cards[ $key ]['name']       = esc_html( $item['name'] );
				$cards[ $key ]['title']      = ! empty( $item['role'] ) ? esc_html( $item['role'] ) : false;
				$cards[ $key ]['summary']    = nl2br( esc_html( $item['summary'] ) );
				$description                 = wp_kses_post( $item['description'] );
				$cards[ $key ]['summary']   .= $description;
				$cards[ $key ]['meta']       = array();

				if ( $item['twitter'] ) {
					$cards[ $key ]['meta']['twitter']['selector']     = 'twitter';
					$cards[ $key ]['meta']['twitter']['text']         = 'Twitter';
					$cards[ $key ]['meta']['twitter']['url']          = esc_url( $item['twitter'] );
					$cards[ $key ]['meta']['twitter']['target_blank'] = true;
				}

				if ( $item['linkedin'] ) {
					$cards[ $key ]['meta']['linkedin']['selector']     = 'linkedin';
					$cards[ $key ]['meta']['linkedin']['text']         = 'LinkedIn';
					$cards[ $key ]['meta']['linkedin']['url']          = esc_url( $item['linkedin'] );
					$cards[ $key ]['meta']['linkedin']['target_blank'] = true;
				}

				if ( $item['link'] ) {
					$cards[ $key ]['meta']['link']['selector']     = 'link';
					$cards[ $key ]['meta']['link']['text']         = 'Link';
					$cards[ $key ]['meta']['link']['url']          = esc_url( $item['link']['url'] );
					$cards[ $key ]['meta']['link']['target_blank'] = true;
				}

				$cards[ $key ] = apply_filters( 'override_manual_profile_item', $cards[ $key ], $item );
			}
		}
		// Restore below if client has CMS type enabled
		//elseif ( 'cms' === $fields['type'] ) {
		//	$posts = $fields['profiles_from_cms'];
		//	if ( $posts ) {
		//		foreach ( $posts as $key => $post ) {
		//			
		//			$cards[ $key ]['image_data'] = $responsive_images->get_image_data( get_field( 'profile_picture', //$post->ID ), 'portrait_image' );
		//			$cards[ $key ]['name']       = esc_html( get_field( 'name', $post->ID ) );
		//			$cards[ $key ]['title']      = esc_html( get_field( 'role', $post->ID ) );
		//			$cards[ $key ]['summary']    = wp_kses_post( get_field( 'biography', $post->ID ) );
		//			$cards[ $key ]['meta']       = array();
//
		//			$contact_channels = get_field( 'contact_channels', $post->ID );
//
		//			if ( $contact_channels['twitter'] ) {
		//				$cards[ $key ]['meta']['twitter']['selector']     = 'twitter';
		//				$cards[ $key ]['meta']['twitter']['text']         = 'Twitter';
		//				$cards[ $key ]['meta']['twitter']['url']          = esc_url( $contact_channels['twitter'] );
		//				$cards[ $key ]['meta']['twitter']['target_blank'] = true;
		//			}
//
		//			if ( $contact_channels['facebook'] ) {
		//				$cards[ $key ]['meta']['facebook']['selector']     = 'facebook';
		//				$cards[ $key ]['meta']['facebook']['text']         = 'Facebook';
		//				$cards[ $key ]['meta']['facebook']['url']          = esc_url( $contact_channels['facebook'] );
		//				$cards[ $key ]['meta']['facebook']['target_blank'] = true;
		//			}
//
		//			if ( $contact_channels['email'] ) {
		//				$cards[ $key ]['meta']['email']['selector']     = 'email';
		//				$cards[ $key ]['meta']['email']['text']         = 'Email';
		//				$cards[ $key ]['meta']['email']['url']          = 'mailto:' . esc_html( $contact_channels['email'] );
		//				$cards[ $key ]['meta']['email']['target_blank'] = true;
		//			}
//
		//			if ( $contact_channels['linkedin'] ) {
		//				$cards[ $key ]['meta']['linkedin']['selector']     = 'linkedin';
		//				$cards[ $key ]['meta']['linkedin']['text']         = 'LinkedIn';
		//				$cards[ $key ]['meta']['linkedin']['url']          = esc_html( $contact_channels['linkedin'] );
		//				$cards[ $key ]['meta']['linkedin']['target_blank'] = true;
		//			}
//
		//			if ( $contact_channels['website'] ) {
		//				$cards[ $key ]['meta']['website']['selector']     = 'link';
		//				$cards[ $key ]['meta']['website']['text']         = 'Website';
		//				$cards[ $key ]['meta']['website']['url']          = esc_html( $contact_channels['website'] );
		//				$cards[ $key ]['meta']['website']['target_blank'] = true;
		//			}
		//			$cards[ $key ] = apply_filters( 'override_cms_profile_item', $cards[ $key ], $post );
		//		}
		//	}
		//}

		return $cards;
	}

	/**
	 * Twiggify the data for information overview items.
	 *
	 * @param array $fields The field data for the block.
	 * @return array $cards Twiggified data as expected by FE template.
	 */
	private function twiggify_information_overview_items( $fields ) {
		$responsive_images = new FB_WP_HC2_Responsive_Images();
		if ( ! empty( $fields['overview_points'] ) ) {
			$cards = array();
			foreach ( $fields['overview_points'] as $key => $point ) {
				$cards[ $key ]['title']      = esc_html( $point['title'] );
				$cards[ $key ]['icon']       = $point['icon'] ? true : false;
				$cards[ $key ]['image_data'] = $responsive_images->get_image_data( $point['icon'], 'card_image' );
				$cards[ $key ]['summary']    = nl2br( esc_html( $point['information'] ) );
			}
			return $cards;
		} else {
			return false;
		}
	}

	/**
	 * Twiggify the data for self selection items.
	 *
	 * @param array $fields The field data for the block.
	 * @return array $cards Twiggified data as expected by FE template.
	 */
	private function twiggify_self_selection_items( $fields ) {
		if ( ! empty( $fields['self_selection_entries'] ) ) {
			$selects = array();
			foreach ( $fields['self_selection_entries'] as $key => $select ) {
				$selects[ $key ]['title'] = esc_html( $select['label'] );
				$selects[ $key ]['value'] = $select['destination'];
			}
			return $selects;
		} else {
			return false;
		}
	}

	/**
	 * Twiggify the data for featured promo items.
	 *
	 * @param array $fields The field data for the block.
	 * @return array $cards Twiggified data as expected by FE template.
	 */
	private function twiggify_featured_promos_items( $fields ) {
		$featured_promos_twiggifier = new FB_Gutenberg_Twiggify_Featured_Promos();
		return $featured_promos_twiggifier->twiggify_featured_promos_items( $fields );
	}

	/**
	 * Adds alignment classes to the fields array.
	 *
	 * @param array $fields The field data for the block.
	 * @param array $post_id The post id where the block is to be rendered.
	 * @return array $fields Updated field data that inclues the alignment classnames.
	 */
	public function add_alignment_classes_to_fields( $fields, $post_id ) {
		$alignment_classes     = array();
		$hide_local_navigation = get_field( 'hide_local_navigation', $post_id );

		if ( ! isset( $fields['alignment'] ) ) {
			return $fields;
		}

		switch ( $fields['alignment'] ) {
			case 'inline':
				$alignment_classes[] = 'inline';
				break;

			case 'inline-centre':
				$alignment_classes[] = 'inline';
				$alignment_classes[] = 'center';
				break;

			case 'full-width-centre':
				$alignment_classes[] = 'center';
				break;

			case 'full-width':
				break;

			default:
				return $fields;
		}

		$fields['alignment_classes'] = implode( ' ', $alignment_classes );

		return $fields;
	}

	/**
	 * Twiggify the data for gallery items.
	 *
	 * @param array $fields The field data for the block.
	 * @return array $gallery Twiggified data as expected by FE template.
	 */
	private function twiggify_gallery_items( $fields ) {
		$gallery = array();
		if ( ! empty( $fields['images'] ) ) {
			$responsive_images = new FB_WP_HC2_Responsive_Images();
			foreach ( $fields['images'] as $key => $gallery_item ) {
				$gallery[ $key ]['image']      = esc_url( $gallery_item['image']['sizes']['gallery_image_large'] );
				$gallery[ $key ]['imagethumb'] = esc_url( $gallery_item['image']['sizes']['logo_image_large'] );
				$gallery[ $key ]['caption']    = esc_html( $gallery_item['caption'] );
				$gallery[ $key ]['source']     = esc_html( $gallery_item['source'] );
				$gallery[ $key ]               = apply_filters( 'override_gallery_item', $gallery[ $key ], $gallery_item );
			}
		}
		$gallery = apply_filters( 'override_gallery_items', $gallery, $fields );
		return $gallery;
	}

	/**
	 * Twiggify the data for simple donation items/links.
	 *
	 * @param array $fields The field data for the block.
	 * @return array $buttons Twiggified data as expected by FE template.
	 */
	private function twiggify_simple_donation_items( $fields ) {
		if ( ! empty( $fields['buttons'] ) ) {
			$buttons = array();
			foreach ( $fields['buttons'] as $key => $button ) {
				if ( ! empty( $button['button']['title'] ) && ! empty( $button['button']['url'] ) ) {
					$buttons[ $key ]['title']  = esc_html( $button['button']['title'] );
					$buttons[ $key ]['path']   = esc_url( $button['button']['url'] );
					$buttons[ $key ]['target'] = esc_attr( $button['button']['target'] );
				}
			}
			return $buttons;
		} else {
			return false;
		}
	}

	/**
	 * Add iframe to the list of allowed tags when wp_kses_post() is used if using the post context.
	 * Note: post is the default context for wp_kses_post().
	 *
	 * @param array        $allowedposttags The allowed tags.
	 * @param string|array $context Context name.
	 *
	 * @return array $allowedposttags Twiggified data as expected by FE template.
	 */
	public function allow_iframes_with_kses_post( $allowedposttags, $context ) {
		if ( 'post' === $context ) {
			$allowedposttags['iframe'] = array(
				'src'             => true,
				'height'          => true,
				'width'           => true,
				'frameborder'     => true,
				'allowfullscreen' => true,
			);
		}
		return $allowedposttags;
	}
}
