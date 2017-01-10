<?php
/**
 * Contains class for the main functions.
 *
 * @package Simple Custom Admin
 * @author Carl Alberto
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class used for the main functions of the plugin.
 *
 * @package Simple Custom Admin
 * @author Carl Alberto
 * @since 1.0.0
 */
class Simple_Custom_Admin {

	/**
	 * The single instance of Simple_Custom_Admin.
	 *
	 * @var 	object
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
	 * @var     string
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
	 * @access public
	 * @param	string $file filename of the plugin.
	 * @param	string $version version number of plugin.
	 * @since   1.0.0
	 * @return  void
	 */
	public function __construct( $file = '', $version = '1.0.0' ) {
		$this->_version = $version;
		$this->_token = 'simple_custom_admin';

		// Load plugin environment variables.
		$this->file = $file;
		$this->dir = dirname( $this->file );
		$this->assets_dir = trailingslashit( $this->dir ) . 'assets';
		$this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );

		$this->script_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		register_activation_hook( $this->file, array( $this, 'install' ) );
		register_deactivation_hook( $this->file, array( $this, 'deactivatethis' ) );

		// Load admin JS & CSS.
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10, 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ), 10, 1 );

		$this->admin_customization_set();
		$this->user_filters();

		// Load API for generic admin functions.
		if ( is_admin() ) {
			$this->admin = new Simple_Custom_Admin_Admin_API();
		}

		// Handle localisation.
		$this->load_plugin_textdomain();
		add_action( 'init', array( $this, 'load_localisation' ), 0 );

	} // End __construct ()

	/**
	 * Initiates all settings.
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function user_filters() {
		$this->check_setting( 'csa1_checkbox_settings' );
		$this->check_setting( 'csa1_checkbox_user_edit' );
		$this->check_setting( 'csa1_checkbox_plugin_edit' );
		$this->check_setting( 'csa1_checkbox_theme_edit' );
		$this->check_setting( 'csa1_checkbox_update_core' );
		$this->check_setting( 'csa1_checkbox_remove_tools' );
		$this->check_setting( 'csa1_checkbox_disable_posts' );
		$this->check_setting( 'csa1_checkbox_disable_pages' );
		$this->check_setting( 'csa1_checkbox_disable_media' );
		$this->check_setting( 'csa1_checkbox_disable_comments' );
	}

	/**
	 * Initiates all settings.
	 *
	 * @access  public
	 * @param	string $setting_name Name of the set of options to check.
	 * @since   1.0.0
	 */
	public function check_setting( $setting_name ) {
		$user_role = $this->role_edit();
		switch ( $setting_name ) {
			case 'csa1_checkbox_settings' :
				$user_role->remove_manage_options( $this->check_if_enabled( $setting_name ) );
				break;

			case 'csa1_checkbox_user_edit' :
				$user_role->remove_manage_users( $this->check_if_enabled( $setting_name ) );
				break;

			case 'csa1_checkbox_plugin_edit' :
				$user_role->remove_manage_plugins( $this->check_if_enabled( $setting_name ) );
				break;

			case 'csa1_checkbox_theme_edit' :
				$user_role->remove_manage_theme( $this->check_if_enabled( $setting_name ) );
				break;

			case 'csa1_checkbox_update_core' :
				$user_role->remove_update_core( $this->check_if_enabled( $setting_name ) );
				break;

			case 'csa1_checkbox_remove_tools' :
				$user_role->remove_tools( $this->check_if_enabled( $setting_name ) );
				break;

			case 'csa1_checkbox_disable_posts' :
				$user_role->disable_posts( $this->check_if_enabled( $setting_name ) );
				break;

			case 'csa1_checkbox_disable_pages' :
				$user_role->disable_pages( $this->check_if_enabled( $setting_name ) );
				break;

			case 'csa1_checkbox_disable_media' :
				$user_role->disable_media( $this->check_if_enabled( $setting_name ) );
				break;

			case 'csa1_checkbox_disable_comments' :
				$user_role->disable_comments( $this->check_if_enabled( $setting_name ) );
				break;

			default:
				break;
		}
	}

	/**
	 * Checks what options are enabled.
	 *
	 * @access  public
	 * @since   1.0.0
	 */
	public function admin_customization_set() {
		// Load custom admin logo.
		if ( ! ( empty( get_option( 'csa1_login_image' ) ) ) ) {
			add_action( 'login_enqueue_scripts', array( $this, 'add_custom_login' ), 10, 1 );
			add_filter( 'login_headerurl', array( $this, 'my_login_logo_url' ), 10, 1 );
			add_filter( 'login_headertitle', array( $this, 'my_login_logo_url_title' ), 10, 1 );
		}

		// Load dashboard customization.
		if ( ! ( empty( get_option( 'csa1_dashboard_image' ) ) ) ) {
			add_action( 'admin_bar_menu', array( $this, 'remove_wp_logo' ), 999 );
			add_action( 'admin_bar_menu', array( $this, 'dashboard_custom_logo' ), 999 );
		}

		// Load footer text.
		if ( ! ( empty( get_option( 'csa1_admin_footer' ) ) ) ) {
			add_action( 'admin_footer_text', array( $this, 'remove_footer_admin' ), 999 );
			add_action( 'admin_menu', array( $this, 'hide_admin_footer_wp_version' ), 999 );
		}

		// This will remove the dropdown WP help tab.
		if ( ! ( empty( get_option( 'csa1_checkbox_remove_help' ) ) ) ) {
			add_filter( 'contextual_help', array( $this, 'remove_helptabs' ), 999, 3 );
		}

		// This will remove the default dashboard widgets.
		if ( ! ( empty( get_option( 'csa1_checkbox_remove_dashboard_widgets' ) ) ) ) {
			add_action( 'admin_init', array( $this, 'remove_dashboard_meta' ), 999 );
		}

		// This will display a custom admin dashboard widget.
		if ( ! ( empty( get_option( 'csa1_checkbox_remove_dashboard_widgets' ) ) ) ) {
			add_action( 'wp_dashboard_setup', array( $this, 'add_custom_dashboard_widget' ), 999 );
		}

	}

	/**
	 * Wrapper function to register a new user role.
	 *
	 * @return object              class object
	 */
	public function access_class_admin_role() {
		return new Simple_Custom_Admin_Roles();
	}

	/**
	 * Wrapper function to modify user roles
	 *
	 * @return object              class object
	 */
	public function role_edit() {
		return $this->access_class_admin_role();
	}

	/**
	 * Removes the Help tab in the WP Admin.
	 *
	 * @param array $old_help	Admin help tab.
	 * @param int	$screen_id	Screen iD.
	 * @param obj	$screen	Current screen.
	 * @return array
	 */
	public function remove_helptabs( $old_help, $screen_id, $screen ) {
		$screen->remove_help_tabs();
		return $old_help;
	}

	/**
	 * Function that clears up the default admin dashboard widget.
	 *
	 * @return void
	 */
	public function remove_dashboard_meta() {
		if ( ! ( current_user_can( 'manage_options' ) ) ) {
			remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'normal' );
			remove_meta_box( 'dashboard_plugins', 'dashboard', 'normal' );
			remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
			remove_meta_box( 'dashboard_secondary', 'dashboard', 'normal' );
			remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'side' );
			remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
			remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );
			remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
			remove_action( 'welcome_panel', 'wp_welcome_panel' );
		}
	}

	/**
	 * Adds the custom admin dashboard widget.
	 *
	 * @return void
	 */
	public function add_custom_dashboard_widget() {
		if ( ! ( empty( get_option( 'csa1_dashboard_title_1' ) ) ) ) {
			$widget_title = get_option( 'csa1_dashboard_title_1' );
		} else {
			$widget_title = 'Dashboard Shortcuts' ;
		}
		wp_add_dashboard_widget(
			'example_dashboard_widget',         // Widget slug.
			$widget_title,
			array( $this, 'get_dashboard_body' )
		);
	}

	/**
	 * Function that gets the body of the custom dashboard widget.
	 *
	 * @return void
	 */
	public function get_dashboard_body() {
		if ( ! ( empty( get_option( 'csa1_dashboard_content_1' ) ) ) ) {
			echo esc_html( get_option( 'csa1_dashboard_content_1' ) );
		} else {
			echo 'Please Customize Body in the Settings Page' ;
		}
	}

	/**
	 * Function that gets the body of the custom dashboard widget.
	 *
	 * @param	string $option_name Name of the option to be checked if enabled.
	 * @return	boolean	Return true if enabled.
	 */
	public function check_if_enabled( $option_name ) {
		if ( ! ( empty( get_option( $option_name ) ) ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Assign login image if uploaded.
	 *
	 * @return null.
	 */
	public function add_custom_login() {
		$header_login = wp_get_attachment_url( get_option( 'csa1_login_image' ) );
		$img_size = wp_get_attachment_image_src( get_option( 'csa1_login_image' ) );
		?>
			<style type="text/css">
			body.login div#login h1 a {
			background-image: url( <?php echo esc_html( $header_login ) ?>);
			width: 100%;
			background-size: contain;
			</style>
		<?php
		return null;
	}

	/**
	 * Assign admin dashboard logo if uploaded.
	 *
	 * @return void
	 */
	public function dashboard_custom_logo() {
		$dashboard_img = wp_get_attachment_url( get_option( 'csa1_dashboard_image' ) );
		$img_size = wp_get_attachment_image_src( get_option( 'csa1_dashboard_image' ) );
		?>
			<style type="text/css">
			#wp-admin-bar-wp-logo, #wpadminbar #wp-admin-bar-site-name > .ab-item:before {
				background-image: url(<?php echo esc_html( $dashboard_img ) ?>) !important;
				background-size: cover;
				background-position: 0 0;
				color:rgba(0, 0, 0, 0);
				width: <?php echo esc_html( $img_size[2] ) ?>px;
			}
			</style>
		<?php
	}

	/**
	 * Displays a custom footer text.
	 *
	 * @return void
	 */
	public function remove_footer_admin() {
		echo esc_html( get_option( 'csa1_admin_footer' ) );
	}

	/**
	 * Removes the WP version in the admin footer.
	 *
	 * @return void
	 */
	public function hide_admin_footer_wp_version() {
		if ( ! ( current_user_can( 'manage_options' ) ) ) {
	        remove_filter( 'update_footer', 'core_update_footer' );
	    }
	}

	/**
	 * Hides the WP logo in the admin panel for a more focused admin experience.
	 *
	 * @param array $wp_admin_bar The current admin bar node.
	 * @return void
	 */
	public function remove_wp_logo( $wp_admin_bar ) {
		$wp_admin_bar->remove_node( 'comments' );
		$wp_admin_bar->remove_node( 'wp-logo' );
	}

	/**
	 * Modifies the home URL in the admin login.
	 *
	 * @return the home_url.
	 */
	public function my_login_logo_url() {
	    return home_url();
	}

	/**
	 * Modifies the blog title name in the admin bar.
	 *
	 * @return the blog name.
	 */
	public function my_login_logo_url_title() {
		return get_bloginfo( 'name' );
	}

	/**
	 * Load admin CSS.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @param	string $hook Name of the hook.
	 * @return  void
	 */
	public function admin_enqueue_styles( $hook = '' ) {
		wp_register_style( $this->_token . '-admin', esc_url( $this->assets_url ) . 'css/admin.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-admin' );
	} // End admin_enqueue_styles ()

	/**
	 * Load admin Javascript.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @param	string $hook Name of the hook.
	 * @return  void
	 */
	public function admin_enqueue_scripts( $hook = '' ) {
		wp_register_script( $this->_token . '-admin', esc_url( $this->assets_url ) . 'js/admin' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version );
		wp_enqueue_script( $this->_token . '-admin' );
	} // End admin_enqueue_scripts ()

	/**
	 * Load plugin localisation.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_localisation() {
		load_plugin_textdomain( 'simple-custom-admin', false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_localisation ()

	/**
	 * Load plugin textdomain.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_plugin_textdomain() {
	    $domain = 'simple-custom-admin';
	    $locale = apply_filters( 'plugin_locale', get_locale(), $domain );
	    load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
	    load_plugin_textdomain( $domain, false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_plugin_textdomain ()

	/**
	 * Main Simple_Custom_Admin Instance
	 *
	 * Ensures only one instance of Simple_Custom_Admin is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see Simple_Custom_Admin()
	 * @param	string $file filename of the plugin.
	 * @param	string $version version number of plugin.
	 * @return Main Simple_Custom_Admin instance
	 */
	public static function instance( $file = '', $version = '1.0.0' ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $file, $version );
		}
		return self::$_instance;
	} // End instance ()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, esc_html_e( 'Cheatin&#8217; huh?' ) );
	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html_e( 'Cheatin&#8217; huh?' ) );
	} // End __wakeup ()

	/**
	 * Installation. Runs on activation.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function install() {
		$this->_log_version_number();
		if ( ! ( empty( get_option( 'csa1_user_role_name' ) ) ) ) {
			$role_name = get_option( 'csa1_user_role_name' );
		} else {
			$role_name = 'Manager' ;
			add_option( 'csa1_user_role_name', $role_name );
		}
		$this->register_new_role( $role_name );

	} // End install ()

	/**
	 * Wrapper function to register a new user role.
	 *
	 * @param  string $role_name   Role name.
	 * @return void
	 */
	public function register_new_role( $role_name ) {
		$new_user_role = new Simple_Custom_Admin_Roles();
		$new_user_role->add_custom_role( $role_name );
	}

	/**
	 *  Runs when plugin is deactivated.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function deactivatethis() {
		if ( ! ( empty( get_option( 'csa1_reset_checkbox' ) ) ) ) {
			$access_class = $this->access_class_admin_role();
			$access_class->clear_all_items();
		}
	}

	/**
	 * Log the plugin version number.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	private function _log_version_number() {
		update_option( $this->_token . '_version', $this->_version );
	} // End _log_version_number ()

}
