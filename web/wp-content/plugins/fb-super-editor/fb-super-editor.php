<?php
/**
 * Plugin Name: FB Super editor
 * Plugin URI: http://fatbeehive.com
 * Description: This plugin creates the role super editor ( Editor role + User management capabilities )
 * Author: Fat Beehive
 * Author URI: http://fatbeehive.com
 * Version: 1.0
 *
 * @package FB Utilities.
 */

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );
define( 'FB_SUPER_EDITOR', plugin_dir_url( __FILE__ ) . 'js/' );

require_once __dir__ . '/classes/class-fb-super-editor.php';

$fb_super_editor = (
	isset( $fb_super_editor ) && is_object( $fb_super_editor ) ) ?
		$fb_super_editor :
		new Fb_Super_Editor();
