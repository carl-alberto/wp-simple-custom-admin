<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class Simple_Custom_Admin_Roles {

	/**
	 * The single instance of Simple_Custom_Admin_Roles.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * Settings class object
	 * @var     object
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = null;

	/**
	 * The version number.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_version;

	/**
	 * The token.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_token;

	/**
	 * The main plugin file.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $file;

	/**
	 * The main plugin directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $dir;

	/**
	 * The plugin assets directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_dir;

	/**
	 * The plugin assets URL.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_url;

	/**
	 * Suffix for Javascripts.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $script_suffix;

	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function __construct ( ) {

	} // End __construct ()

	/**
	 * Clears all data
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function clear_all_items () {
		delete_option( 'csa1_reset_checkbox' );
		delete_option( 'csa1_login_image' );
		delete_option( 'csa1_dashboard_image' );
		delete_option( 'csa1_user_role_name' );
		delete_option( 'csa1_admin_footer' );

		delete_option( 'csa1_checkbox_settings' );
		delete_option( 'csa1_checkbox_remove_help' );

		delete_option( 'csa1_checkbox_remove_dashboard_widgets' );
		delete_option( 'csa1_dashboard_title_1' );
		delete_option( 'csa1_dashboard_content_1' );

		remove_role( 'manager' );
		remove_role( 'Manager' );
		if ( !empty ( get_option( 'csa1_user_role_name' ) ) ) {
			$role_name = get_option( 'csa1_user_role_name' );
			remove_role( $role_name );
		}
	}

	/**
	 * Register a new user role
	 * @param  string $role_name   Role name
	 * @return void
	 */
	public function add_custom_role ( $role_name ) {
		add_role(
			$role_name,
			$role_name,
			$this->default_admin_capabilities()
		);
	}

	/**
	 * Gets the capabilites of the administrator role
	 * @return array of admin rolse
	 */
	public function default_admin_capabilities () {
		$admin_role_set = get_role( 'administrator' )->capabilities;
		return $admin_role_set;
	}

	/**
	 * Remove capabilites to  custom role assignede in the plugin
	 * @param  array $caps  Array of capabilites see https://codex.wordpress.org/Roles_and_Capabilities
	 * @param  string $remove  if set to false, it will add the capability back
	 * @return void
	 */
	public function remove_capability ( $caps, $remove = true ) {
		$edit_user = get_role( get_option( 'csa1_user_role_name' ) );

		if ( $remove ) {
			foreach ( $caps as $cap ) {
		    	$edit_user->remove_cap( $cap );
		    }
		} else {
			foreach ( $caps as $cap ) {
		    	$edit_user->add_cap( $cap );
		    }
		}
	}

	public function remove_manage_options ( $remove = true ) {
		$caps = array(
				'manage_options',
			);
		$this->remove_capability( $caps, $remove );
	}

	public function remove_manage_users ( $remove = true ) {
		$caps = array(
				'edit_users',
				'create_users',
				'delete_users',
			);
		$this->remove_capability( $caps, $remove );
	}


}
