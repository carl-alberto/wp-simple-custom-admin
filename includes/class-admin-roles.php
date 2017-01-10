<?php
/**
 * Contains class for the admin functions.
 *
 * @package Simple Custom Admin
 * @author Carl Alberto
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class used for the custom roles options.
 *
 * @package Simple Custom Admin
 * @author Carl Alberto
 * @since 1.0.0
 */
class Simple_Custom_Admin_Roles {

	/**
	 * The single instance of Simple_Custom_Admin_Roles.
	 *
	 * @var		object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * Settings class object
	 *
	 * @var     object
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = null;

	/**
	 * The version number.
	 *
	 * @var		string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_version;

	/**
	 * The token.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_token;

	/**
	 * The main plugin file.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $file;

	/**
	 * The main plugin directory.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $dir;

	/**
	 * The plugin assets directory.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_dir;

	/**
	 * The plugin assets URL.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_url;

	/**
	 * Suffix for Javascripts.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $script_suffix;

	/**
	 * Constructor function.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function __construct() {

	} // End __construct ()

	/**
	 * Clears all data
	 *
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function clear_all_items() {
		delete_option( 'csa1_reset_checkbox' );
		delete_option( 'csa1_login_image' );
		delete_option( 'csa1_dashboard_image' );
		delete_option( 'csa1_user_role_name' );
		delete_option( 'csa1_admin_footer' );
		delete_option( 'csa1_checkbox_remove_help' );

		delete_option( 'csa1_checkbox_remove_dashboard_widgets' );
		delete_option( 'csa1_dashboard_title_1' );
		delete_option( 'csa1_dashboard_content_1' );

		delete_option( 'csa1_checkbox_settings' );
		delete_option( 'csa1_checkbox_user_edit' );
		delete_option( 'csa1_checkbox_plugin_edit' );
		delete_option( 'csa1_checkbox_theme_edit' );
		delete_option( 'csa1_checkbox_update_core' );
		delete_option( 'csa1_checkbox_remove_tools' );

		delete_option( 'csa1_checkbox_disable_posts' );
		delete_option( 'csa1_checkbox_disable_pages' );
		delete_option( 'csa1_checkbox_disable_media' );
		delete_option( 'csa1_checkbox_disable_comments' );

		remove_role( 'manager' );
		remove_role( 'Manager' );
		if ( ! ( empty( get_option( 'csa1_user_role_name' ) ) ) ) {
			$role_name = get_option( 'csa1_user_role_name' );
			remove_role( $role_name );
		}
	}

	/**
	 * Register a new user role
	 *
	 * @param  string $role_name   Role name.
	 * @return void
	 */
	public function add_custom_role( $role_name ) {
		add_role(
			$role_name,
			$role_name,
			$this->default_admin_capabilities()
		);
	}

	/**
	 * Gets the capabilites of the administrator role
	 *
	 * @return array of admin roles.
	 */
	public function default_admin_capabilities() {
		$admin_role_set = get_role( 'administrator' )->capabilities;
		return $admin_role_set;
	}

	/**
	 * Remove capabilites to  custom role assigned in the plugin.
	 *
	 * @param	array   $caps Array of capabilites see https://codex.wordpress.org/Roles_and_Capabilities.
	 * @param	boolean $remove  if set to false, it will add the capability back.
	 * @return false if $edit_user is empty.
	 */
	public function remove_capability( $caps, $remove = true ) {
		$edit_user = get_role( get_option( 'csa1_user_role_name' ) );
		if ( $edit_user ) {
			if ( ! (empty( $caps ) ) ) {
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
		} else {
			return false ;
		}
	}

	/**
	 * Modifies the existing menu with a specific custom user role.
	 *
	 * @param   array   $menuitems  Array of menu items that will be removed.
	 * @param	boolean $remove if set to false, it will add the menuitem back.
	 * @return false if $edit_user is empty.
	 */
	public function remove_menuitem( $menuitems, $remove = true ) {
		$edit_user = get_role( get_option( 'csa1_user_role_name' ) );
		if ( $remove ) {
			foreach ( $menuitems as $menuitem ) {
				break; // Temporary breaks code. Still needs to be implemented.
				return $menuitem;
			}
		} else {
			foreach ( $menuitems as $menuitem ) {
				$edit_user->add_cap( $cap );
			}
		}
	}

	/**
	 * Remove an array of Administrators capability.
	 *
	 * @param	boolean $remove if set to false, otherwise it will add the capabilities back.
	 */
	public function remove_manage_options( $remove = true ) {
		$caps = array(
				'manage_options',
				'create_sites',
				'delete_sites',
				'manage_network',
				'manage_sites',
				'manage_network_users',
				'manage_network_plugins',
				'manage_network_themes',
				'manage_network_options',
				'delete_site',
			);
		$this->remove_capability( $caps, $remove );
	}

	/**
	 * Remove an array of capability to manage users.
	 *
	 * @param	boolean $remove if set to false, otherwise it will add the capabilities back.
	 */
	public function remove_manage_users( $remove = true ) {
		$caps = array(
				'edit_users',
				'create_users',
				'delete_users',
				'list_users',
				'promote_users',
				'remove_users',
			);
		$this->remove_capability( $caps, $remove );
	}

	/**
	 * Remove an array of capability to manage plugins.
	 *
	 * @param	boolean $remove if set to false, otherwise it will add the capabilities back.
	 */
	public function remove_manage_plugins( $remove = true ) {
		$caps = array(
				'activate_plugins',
				'upload_plugins',
				'delete_plugins',
				'edit_plugins',
				'install_plugins',
			);
		$this->remove_capability( $caps, $remove );
	}

	/**
	 * Remove an array of capability manage plugins.
	 *
	 * @param	boolean $remove if set to false, otherwise it will add the capabilities back.
	 */
	public function remove_manage_theme( $remove = true ) {
		$caps = array(
				'upload_themes',
				'delete_themes',
				'edit_themes',
				'edit_theme_options',
				'install_themes',
				'switch_themes',
				'update_themes',
			);
		$this->remove_capability( $caps, $remove );
	}

	/**
	 * Remove an array of capability to update files and core.
	 *
	 * @param	boolean $remove if set to false, otherwise it will add the capabilities back.
	 */
	public function remove_update_core( $remove = true ) {
		$caps = array(
				'edit_files',
				'update_core',
				'update_plugins',
				'edit_dashboard',
				'customize',
			);
		$this->remove_capability( $caps, $remove );
	}

	/**
	 * Remove an array of capability for the tools.
	 *
	 * @param	boolean $remove if set to false, otherwise it will add the capabilities back.
	 */
	public function remove_tools( $remove = true ) {
		$caps = array(
				'export',
				'import',
			);
		$this->remove_capability( $caps, $remove );
		// Still need to find a function to hide the tools menus in the admin.
	}

	/**
	 * Remove an array of capability to manage posts.
	 *
	 * @param	boolean $remove if set to false, otherwise it will add the capabilities back.
	 */
	public function disable_posts( $remove = true ) {
		$caps = array(
				'edit_others_posts',
				'delete_others_posts',
				'delete_private_posts',
				'edit_private_posts',
				'read_private_posts',
				'edit_published_posts',
				'delete_published_posts',
				'edit_posts',
				'delete_posts',
				'edit_others_posts',
				'unfiltered_html',
				'manage_categories',
			);
		$this->remove_capability( $caps, $remove );
	}

	/**
	 * Remove an array of capability to manage pages.
	 *
	 * @param	boolean $remove if set to false, otherwise it will add the capabilities back.
	 */
	public function disable_pages( $remove = true ) {
		$caps = array(
				'read_private_pages',
				'edit_private_pages',
				'delete_private_pages',
				'delete_published_pages',
				'delete_others_pages',
				'delete_pages',
				'publish_pages',
				'edit_published_pages',
				'edit_others_pages',
				'edit_pages',
				'manage_links',
			);
		$this->remove_capability( $caps, $remove );
	}

	/**
	 * Remove an array of capability to manage media.
	 *
	 * @param	boolean $remove if set to false, otherwise it will add the capabilities back.
	 */
	public function disable_media( $remove = true ) {
		$caps = array(
				'upload_files',
			);
		$this->remove_capability( $caps, $remove );
	}

	/**
	 * Remove an array of capability to manage comments.
	 *
	 * @param	boolean $remove if set to false, otherwise it will add the capabilities back.
	 */
	public function disable_comments( $remove = true ) {
		$caps = array(
				'moderate_comments',
			);
		$this->remove_capability( $caps, $remove );
	}

}
