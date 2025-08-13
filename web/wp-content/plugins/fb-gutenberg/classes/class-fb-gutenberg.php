<?php
/**
 * Plugin Name: FB Gutenberg
 * Plugin URI: http://fatbeehive.com
 * Description: Class for Gutenberg customisations
 * Author: Fat Beehive
 * Author URI: http://fatbeehive.com
 * Version: 1.0
 *
 * @package FB Gutenberg
 */

/**
 * Class FB_Gutenberg
 */
class FB_Gutenberg {

	/**
	 * Post types with no gutenberg blocks.
	 *
	 * @var array $post_types_without_blocks
	 */
	protected $post_types_without_blocks = array(
		'statistic',
	);

	/**
	 * Honeycomb core templates path (Core front end templates).
	 *
	 * @var string $hc_core_templates_path
	 */
	protected $hc_core_templates_path;

	/**
	 * Overrides templates path (Customised front end templates).
	 *
	 * @var string $overrides_templates_path
	 */
	protected $overrides_templates_path;

	/**
	 * Backend templates path.
	 *
	 * @var string $wp_templates_path
	 */
	protected $wp_templates_path;

	/** @var array The final, filtered & sorted list of block slugs */
	protected $allowed_blocks_order = [];

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->hc_core_templates_path   = get_template_directory() . '/honeycom3-fe/templates/';
		$this->overrides_templates_path = get_template_directory() . '/overrides/';
		$this->wp_templates_path        = get_template_directory() . '/templates/';
		add_filter( 'timber/loader/loader', array( $this, 'add_twig_namespaces' ), 10, 1 );
		add_filter( 'allowed_block_types_all', array( $this, 'filter_allowed_block_types' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_fb_gutenberg_admin_css' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_fb_gutenberg_admin_js' ) );
		add_action( 'acf/validate_save_post', array( $this, 'validate_acf_blocks' ), 5 );
		add_filter( 'timber/twig/filters', array( $this, 'add_to_twig' ) );
		add_filter( 'render_block_gravityforms/form', array( $this, 'customise_gravity_form_block' ), 10, 1 );
	}

	/**
	 * Add paths for templates, and associate a namespace for it.
	 *
	 * @param \Twig\Loader\FilesystemLoader $loader .
	 * @return \Twig\Loader\FilesystemLoader $loader .
	 */
	public function add_twig_namespaces( $loader ) {
		$loader->addPath( $this->wp_templates_path, 'wp' );
		$loader->addPath( $this->overrides_templates_path, 'hc' );
		$loader->addPath( $this->hc_core_templates_path, 'hc' );
		$loader->addPath( $this->hc_core_templates_path, 'default' );
		return $loader;
	}

	/**
	 * Customise the available block types.
	 *
	 * @param array  $allowed_block_types Array of block type slugs.
	 * @param object $block_editor_context The current block editor context.
	 * @return array $allowed_block_types Updated array of block type slugs.
	 **/
	public function filter_allowed_block_types( $allowed_block_types, $block_editor_context ) {
		if ( in_array( $block_editor_context->post->post_type, $this->post_types_without_blocks, true ) ) {
			return array();
		}

		$front_page_id = get_option( 'page_on_front' );
		if ( $front_page_id && (int) $front_page_id === $block_editor_context->post->ID ) {
			return array();
		}

		$allowed_block_types = array(
			'gravityforms/form',
			'acf/cta',
			'acf/accordion',
			'acf/statistics',
			'acf/downloads',
			'acf/information-overview',
			'acf/self-selection',
			'acf/profiles',
			'acf/social-share',
			'acf/featured-promos',
			'acf/page-index',
			'acf/local-navigation',
			'acf/quote',
			'acf/media',
			'acf/embed',
			'acf/wysiwyg',
			'acf/gallery',
			'acf/simple-donation',
			'acf/form',
		);
        // Sort alphabetically by slug
        sort( $allowed_block_types, SORT_STRING | SORT_FLAG_CASE );

		// Save for JS
		$this->allowed_blocks_order = $allowed_block_types;

		return $allowed_block_types;
	}

	/**
	 * Enqueue admin CSS.
	 */
	public function enqueue_fb_gutenberg_admin_css() {
		wp_register_style( 'fb_gutenberg_admin_css', plugins_url( '../css/fb-gutenberg-admin.css', __FILE__ ), false, '1.0.0' );
		wp_enqueue_style( 'fb_gutenberg_admin_css' );
		wp_dequeue_style( 'wp-block-styles' );
	}

	/**
	 * Enqueue admin JS.
	 */
	public function enqueue_fb_gutenberg_admin_js()
	{

		// Only load in the block editor
		if (! did_action('enqueue_block_editor_assets')) {
			return;
		}
		$src  = plugins_url('../js/fb-gutenberg-admin.js', __FILE__);
		$deps = array('wp-blocks', 'wp-dom-ready', 'wp-edit-post');
		wp_enqueue_script('fb-gutenberg-admin-js', $src, $deps, '1.0.0', true);

		$allowed = $this->allowed_blocks_order;
		if ( empty( $allowed ) ) {
		  $allowed = $this->filter_allowed_block_types(
			[], 
			(object)[ 'post' => get_post() ]
		  );
		}
		
		// Pass our sorted slugs to JS
		wp_localize_script(
			'fb-gutenberg-admin-js',
			'FB_ALLOWED_BLOCKS',
			$this->allowed_blocks_order
		);
	}

	/**
	 * Make sure ACF fields are validated.
	 **/
	public function validate_acf_blocks() {
		$validate_block_fields = array();
		foreach ( $_POST as $key => $value ) {
			if ( 'acf-' === substr( $key, 0, 4 ) ) {
				$validate_block_fields[ $key ] = $value;
			}
		}

		if ( count( $validate_block_fields ) === 0 ) {
			return;
		}

		foreach ( $validate_block_fields as $key => $value ) {
			acf_validate_values( $value, $key );
		}
	}

	/**
	 * Twig filter for removing default as value from background colour fields.
	 *
	 * @param array $filters filters provided by timber.
	 * @return @array $filters.
	 */
	public function add_to_twig( $filters ) {
		$filters['get_background_class_name'] = array(
			'callable' => array( $this, 'filter_background_colour_value' ),
		);
		return $filters;
	}

	/**
	 * Remove 'default' from background colour field value before use as a CSS classname.
	 *
	 * @param string $value background colour field value.
	 * @return string $value filtered background colour field value.
	 */
	public function filter_background_colour_value( $value ) {
		if ( ! empty( $value ) ) {
			return esc_html( str_ireplace( 'default', '', $value ) );
		} else {
			return $value;
		}
	}

	/**
	 * Wrap gravityforms/form blocks in a HC3 markup.
	 *
	 * @param  string $block_content HTML markup of the block.
	 * @return string Modified block content.
	 */
	public function customise_gravity_form_block( $block_content ) {
		$content = '<section class="wysiwyg-outer section"><div class="container"><div class="content">' . $block_content . '</div></div></section>';
		return $content;
	}
}
