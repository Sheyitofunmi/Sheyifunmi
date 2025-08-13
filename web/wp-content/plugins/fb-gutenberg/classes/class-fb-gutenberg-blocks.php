<?php
/**
 * Plugin Name: FB Gutenberg Blocks
 * Plugin URI: http://fatbeehive.com
 * Description: Custom Gutenberg Blocks
 * Author: Fat Beehive
 * Author URI: http://fatbeehive.com
 * Version: 1.0
 *
 * @package FB Gutenberg Blocks
 */

/**
 * Class FB_Gutenberg_Blocks
 */
class FB_Gutenberg_Blocks {

	/**
	 * List of all blocks and versions.
	 * Names must match the folder name and css filename.
	 * Version is used by WP as a query string, useful for clearing caches.
	 *
	 * @var array $post_types_without_blocks
	 */
	protected $hc3_blocks = array(
		'cta'                  => '1.0',
		'accordion'            => '1.0',
		'statistics'           => '1.0',
		'donate'               => '1.0',
		'impact-overview'      => '1.0',
		'downloads'            => '1.0',
		'information-overview' => '1.0',
		'self-selection'       => '1.0',
		'profiles'             => '1.0',
		'social-share'         => '1.0',
		'featured-promos'      => '1.0',
		'page-index'           => '1.0',
		'quote'                => '1.0',
		'media'                => '1.0',
		'embed'                => '1.0',
		'wysiwyg'              => '1,0',
		'gallery'              => '1,0',
		'simple-donation'      => '1.0',
		'form'                 => '1.0',
	);

	/**
	 * List of all client specific blocks and versions.
	 * Names must match the folder name and css filename.
	 * Version is used by WP as a query string, useful for clearing caches.
	 *
	 * @var array $post_types_without_blocks
	 */
	protected $hc3_theme_level_blocks = array();

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_hc3_acf_blocks' ) );
		// Uncomment to enqueue block CSS when/if required.
		// add_action( 'wp_enqueue_scripts', array( $this, 'register_styles' ) );
	}

	/**
	 * Register hc3 blocks.
	 */
	public function register_hc3_acf_blocks() {
		foreach ( $this->hc3_blocks as $block => $version ) {
			register_block_type( __DIR__ . '/../blocks/' . $block );
		}

		$this->hc3_theme_level_blocks = apply_filters( 'fb_hc3_theme_level_blocks', $this->hc3_theme_level_blocks );

		if ( ! empty( $this->hc3_theme_level_blocks ) ) {
			foreach ( $this->hc3_theme_level_blocks as $block => $version ) {
				$result = register_block_type( get_template_directory() . '/gutenberg-blocks/' . $block );
			}
		}
	}

	/**
	 * Register styles for custom blocks.
	 */
	public function register_styles() {
		foreach ( $this->hc3_blocks as $block => $version ) {
			$handle = 'fb-gutenberg-' . $block;
			$src    = plugin_dir_url( __FILE__ ) . '../blocks/' . $block . '/css/' . $block . '.css';
			wp_register_style( $handle, $src, array(), $version );
		}

		if ( ! empty( $this->hc3_theme_level_blocks ) ) {
			foreach ( $this->hc3_theme_level_blocks as $block => $version ) {
				$handle = 'fb-gutenberg-' . $block;
				$src    = get_template_directory() . '/gutenberg-blocks/' . $block . '/css/' . $block . '.css';
				wp_register_style( $handle, $src, array(), $version );
			}
		}
	}
}
