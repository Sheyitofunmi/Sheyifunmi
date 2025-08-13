<?php
/**
 * Class Fb_Super_Editor
 */
class Fb_Super_Editor {
	/**
	 * Capabilities for super editors in addition to those of editors.
	 *
	 * @var array $needed_caps
	 */
	private $additional_capabilities = array();

	/**
	 * Users management screens on wp-admin.
	 *
	 * @var string[] $user_screens
	 */
	private $user_screens = array(
		'user-new.php',
		'users.php',
		'user-edit.php',
	);

	/**
	 * Fb_Super_Editor constructor.
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'initialise_additional_capabilities' ) );
		add_action( 'after_setup_theme', array( $this, 'add_super_editor_role' ) );
		add_action( 'delete_user', array( $this, 'prevent_admin_users_from_deletion' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_load_script' ) );
		add_filter( 'user_row_actions', array( $this, 'prevent_admin_accounts_from_being_edited' ), 10, 2 );
		add_filter( 'users_list_table_query_args', array( $this, 'remove_admin_users_from_listing' ) );
		add_action( 'after_setup_theme', array( $this, 'append_editor_role' ), 20 );
		add_action( 'admin_menu', array( $this, 'remove_settings_submenus_for_non_admins' ), 100 );
	}

	/**
	 * List of capabilities required in additional to those of editors.
	 */
	public function initialise_additional_capabilities() {
		$additional_capabilities = array(
			'list_users'             => 1,
			'create_users'           => 1,
			'edit_users'             => 1,
			'delete_users'           => 1,
			'promote_users'          => 1,
			'edit_theme_options'     => 1,
			'manage_options'         => 1,
			'manage_privacy_options' => 1,
		);

		if ( function_exists( 'is_plugin_active' ) && is_plugin_active( 'gravityforms/gravityforms.php' ) ) {
			$gform_capabilities = array(
				'gravityforms_edit_forms'       => 1,
				'gravityforms_delete_forms'     => 1,
				'gravityforms_create_form'      => 1,
				'gravityforms_view_entries'     => 1,
				'gravityforms_edit_entries'     => 1,
				'gravityforms_delete_entries'   => 1,
				'gravityforms_view_settings'    => 1,
				'gravityforms_edit_settings'    => 1,
				'gravityforms_export_entries'   => 1,
				'gravityforms_view_entry_notes' => 1,
				'gravityforms_edit_entry_notes' => 1,
			);

			if ( is_plugin_active( 'wp-ultimate-csv-importer-pro/wp-ultimate-csv-importer-pro.php' ) ) {
				$csv_importer_capabilities = array(
					'csv_importer_pro' => 1,
				);

				$additional_capabilities = array_merge( $additional_capabilities, $csv_importer_capabilities );
			}

			$additional_capabilities = array_merge( $additional_capabilities, $gform_capabilities );
		}

		$this->additional_capabilities = $additional_capabilities;
	}

	/**
	 * Assign editor role to super editors.
	 * Because some plugins check roles instead of capabilities.
	 */
	public function append_editor_role() {
		$user = wp_get_current_user();
		if ( $user && in_array( 'super_editor', $user->roles ) ) {
			$user->add_role( 'editor' );
		}
	}

	/**
	 * Create Super editor role.
	 */
	public function add_super_editor_role() {
		global $wp_roles;

		$editor       = $wp_roles->get_role( 'editor' );
		$super_editor = $wp_roles->get_role( 'super_editor' );

		if ( $editor && is_null( $super_editor ) ) {
			// Editor role exists. Super Editor role does not exist.
			$super_editor_caps = array_merge( $editor->capabilities, $this->additional_capabilities );
			add_role( 'super_editor', 'Super editor', $super_editor_caps );
		} elseif ( $editor && ! is_null( $super_editor ) ) {
			// Both Editor and Super Editor roles exist.
			// Add additional capabilities.
			if ( $super_editor->capabilities ) {
				foreach ( $this->additional_capabilities as $capability => $value ) {
					if ( 1 === $value ) {
						$super_editor->add_cap( $capability );
					}
				}
			}
		}
	}

	/**
	 * Prevent admin users from deletion by super editors.
	 *
	 * @param int $user_id ID of the user to delete.
	 */
	public function prevent_admin_users_from_deletion( $user_id ) {
		if ( current_user_can( 'super_editor' ) ) {
			$user_obj = get_userdata( $user_id );
			if ( is_array( $user_obj->roles ) && in_array( 'administrator', $user_obj->roles ) ) {
				die( 'Permission denied : Cannot delete admin accounts' );
			}
		}
	}

	/**
	 * Add JS to user-new.php
	 *
	 * @param string $hook_suffix  The current admin page.
	 */
	public function admin_load_script( $hook_suffix ) {
		if ( ! current_user_can( 'super_editor' ) ) {
			return;
		}

		wp_enqueue_script(
			'fb-super-editor-js',
			FB_SUPER_EDITOR . 'fb-super-editor.js',
			array( 'jquery' ),
			'1.0',
			true
		);
	}

	/**
	 * Prevent Super editor role from deleting and editing admin accounts.
	 *
	 * @param string[] $actions     An array of action links to be displayed.
	 * @param WP_User  $user_object WP_User object for the currently listed user.
	 * @return string[] $actions .
	 */
	public function prevent_admin_accounts_from_being_edited( $actions, $user_object ) {
		$current_user = wp_get_current_user();
		if ( in_array( 'super_editor', (array) $current_user->roles ) && in_array( 'administrator', (array) $user_object->roles ) ) {
				unset( $actions['edit'], $actions['delete'], $actions['view'] );
		}

		return $actions;
	}

	/**
	 * Remove admin users from listing on wp-admin.
	 *
	 * @param array $users_args .
	 * @return array $users_args .
	 */
	public function remove_admin_users_from_listing( $users_args ) {
		if ( current_user_can( 'super_editor' ) ) {
			$users_args['role__not_in'] = array( 'administrator' );
		}

		return $users_args;
	}

	/**
	 * Remove settings menu for non admins.
	 */
	public function remove_settings_submenus_for_non_admins() {
		$user = wp_get_current_user();
		if ( ! in_array( 'administrator', (array) $user->roles, true ) ) {
			remove_menu_page( 'options-general.php' );
		}
	}
}
