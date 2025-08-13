<?php
/**
 * Plugin Name: FB WP HC2 Responsive Images
 * Plugin URI: http://fatbeehive.com
 * Description: Class for preparing responsive images data
 * Author: Fat Beehive
 * Author URI: http://fatbeehive.com
 * Version: 1.0
 *
 * @package FB WP HC2 Responsive Images
 */

/**
 * Class FB_WP_HC2_Responsive_Images
 */
class FB_WP_HC2_Responsive_Images {
	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'after_setup_theme', array( $this, 'add_image_sizes' ) );
		add_filter( 'timber/twig/functions', array( $this, 'add_to_twig' ) );
	}

	/**
	 * Twig function for responsive images.
	 *
	 * @param array $functions Functions provided by timber.
	 * @return @array $functions.
	 */
	public function add_to_twig( $functions ) {
		if ( version_compare( Timber::$version, '2.0.0', '>=' ) ) {
			// Timber 2.x is installed.
			$functions['get_image_data'] = array(
				'callable' => array( $this, 'get_image_data' ),
			);
		} else {
			$functions->addFunction( new Timber\Twig_Function( 'get_image_data', array( $this, 'get_image_data' ) ) );
		}
		return $functions;
	}

	/**
	 * Define image sizes for WP.
	 */
	public function add_image_sizes() {
		add_image_size( 'hero_image_x_large', 2560, 1440, true );
		add_image_size( 'hero_image_large', 1920, 1080, true );
		add_image_size( 'hero_image_medium', 1067, 600, true );
		add_image_size( 'hero_image_small', 622, 350, true );

		add_image_size( 'portrait_image_large', 800, 1800, false );
		add_image_size( 'portrait_image_medium', 600, 1350, false );
		add_image_size( 'portrait_image_small', 400, 600, false );

		add_image_size( 'card_image_large', 1292, 1400, true );
		add_image_size( 'card_image_medium', 969, 1050, true );
		add_image_size( 'card_image_small', 646, 700, true );

		add_image_size( 'landscape_image_large', 1300, 900, true );
		add_image_size( 'landscape_image_medium', 650, 450, true );
		add_image_size( 'landscape_image_small', 325, 225, true );

		// Image sizes for logo park block.
		// Also used for gallery thumbnails.
		add_image_size( 'logo_image_large', 800, 800, true );
		add_image_size( 'logo_image_medium', 512, 512, true );
		add_image_size( 'logo_image_small', 256, 256, true );

		add_image_size( 'gallery_image_large', 2560, 1440, false );
		add_image_size( 'gallery_image_medium', 1920, 1080, false );
		add_image_size( 'gallery_image_small', 1067, 600, false );
	}

	/**
	 * Get Image data for a particular style
	 *
	 * @param int    $image_id id of the image.
	 * @param string $style the image size name.
	 */
	public function get_image_data( $image_id, $style ) {
		$images = array();

		$image_data_small  = wp_get_attachment_image_src( $image_id, $style . '_small' );
		$image_data_medium = wp_get_attachment_image_src( $image_id, $style . '_medium' );
		$image_data_large  = wp_get_attachment_image_src( $image_id, $style . '_large' );
		$image_data_xlarge = wp_get_attachment_image_src( $image_id, $style . '_x_large' ) ?? false;

		if ( $image_data_small ) {
			$width                            = $image_data_small[1];
			$images['srcset'][ $width . 'w' ] = $image_data_small[0];
		}

		if ( $image_data_medium ) {
			$width                            = $image_data_medium[1];
			$images['srcset'][ $width . 'w' ] = $image_data_medium[0];
		}

		if ( $image_data_large ) {
			$width                            = $image_data_large[1];
			$images['srcset'][ $width . 'w' ] = $image_data_large[0];
			$images['fallback']               = $image_data_large[0];
		}

		if ( $image_data_xlarge ) {
			$width                            = $image_data_xlarge[1];
			$images['srcset'][ $width . 'w' ] = $image_data_xlarge[0];
			$images['fallback']               = $image_data_xlarge[0];
		}

		$image_alt = get_post_meta( $image_id, '_wp_attachment_image_alt', true );

		if ( $image_alt ) {
			$images['alt'] = $image_alt;
		}

		// set webp path version
		if ( defined( 'FB_WEBP_PATH' ) ) {
			foreach ( $images['srcset'] as $bp => $value ) {
				$images['srcset'][ $bp ] = str_replace( '/wp-content', FB_WEBP_PATH, $value ) . '.webp';
			}
		}

		return $images;
	}
}
