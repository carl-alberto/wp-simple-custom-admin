<?php
/**
 * Contains class for the admin functions settings.
 *
 * @package Simple Custom Admin
 * @author Carl Alberto
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class used for the main functions of the admin settings.
 *
 * @package Simple Custom Admin
 * @author Carl Alberto
 * @since 1.0.0
 */
class Simple_Custom_Admin_Settings {

	/**
	 * The single instance of Simple_Custom_Admin_Settings.
	 *
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * The main plugin object.
	 *
	 * @var 	object
	 * @access  public
	 * @since 	1.0.0
	 */
	public $parent = null;

	/**
	 * Prefix for plugin settings.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $base = '';

	/**
	 * Available settings for plugin.
	 *
	 * @var     array
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = array();

	/**
	 * Constructor function.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @param	object $parent This constructs the plugin.
	 * @return  void
	 */
	public function __construct( $parent ) {
		$this->parent = $parent;

		$this->base = 'csa1_';

		// Initialise settings.
		add_action( 'init', array( $this, 'init_settings' ), 11 );

		// Register plugin settings.
		add_action( 'admin_init' , array( $this, 'register_settings' ) );

		// Add settings page to menu.
		add_action( 'admin_menu' , array( $this, 'add_menu_item' ) );

		// Add settings link to plugins page.
		add_filter( 'plugin_action_links_' . plugin_basename( $this->parent->file ) , array( $this, 'add_settings_link' ) );

	}

	/**
	 * Initialise settings
	 *
	 * @return void
	 */
	public function init_settings() {
		$this->settings = $this->settings_fields();
	}

	/**
	 * Add settings page to admin menu.
	 *
	 * @return void
	 */
	public function add_menu_item() {
		$page = add_options_page(
			__( 'Simple Custom Admin Settings', 'simple-custom-admin' ),
			__( 'Custom Admin Settings', 'simple-custom-admin' ),
			'manage_options',
			$this->parent->_token . '_settings' ,
			array(
				$this,
				'settings_page',
			)
		);

		add_action( 'admin_print_styles-' . $page, array( $this, 'settings_assets' ) );
	}

	/**
	 * Load settings JS & CSS
	 *
	 * @return void
	 */
	public function settings_assets() {

		// We're including the farbtastic script & styles here because they're needed for the colour picker
		// If you're not including a colour picker field then you can leave these calls out as well as the farbtastic dependency for the wpt-admin-js script below.
		wp_enqueue_style( 'farbtastic' );
		wp_enqueue_script( 'farbtastic' );

		// We're including the WP media scripts here because they're needed for the image upload field.
		// If you're not including an image upload then you can leave this function call out.
		wp_enqueue_media();

		wp_register_script( $this->parent->_token . '-settings-js', $this->parent->assets_url . 'js/settings' . $this->parent->script_suffix . '.js', array( 'farbtastic', 'jquery' ), '1.0.0' );
		wp_enqueue_script( $this->parent->_token . '-settings-js' );
	}

	/**
	 * Add settings link to plugin list table
	 *
	 * @param  array $links Existing links.
	 * @return array 		Modified links
	 */
	public function add_settings_link( $links ) {
		$settings_link = '<a href="options-general.php?page=' . $this->parent->_token . '_settings">' . __( 'Settings', 'simple-custom-admin' ) . '</a>';
		array_push( $links, $settings_link );
		return $links;
	}

	/**
	 * Build settings fields
	 *
	 * @return array Fields to be displayed on settings page.
	 */
	private function settings_fields() {
		$settings['display'] = array(
			'title'					=> __( 'Display', 'simple-custom-admin' ),
			'description'			=> __( 'These settings manipulate the look of the WP Admin Dashboard', 'simple-custom-admin' ),
			'fields'				=> array(
				array(
					'id' 			=> 'login_image',
					'label'			=> __( 'Login Logo' , 'simple-custom-admin' ),
					'description'	=> __( 'Optimal size is 260px x 64px', 'simple-custom-admin' ),
					'type'			=> 'image',
					'default'		=> '',
					'placeholder'	=> '',
				),
				array(
					'id' 			=> 'dashboard_image',
					'label'			=> __( 'Dashboard Logo' , 'simple-custom-admin' ),
					'description'	=> __( 'Optimal size is 64px x 64px', 'simple-custom-admin' ),
					'type'			=> 'image',
					'default'		=> '',
					'placeholder'	=> '',
				),
				array(
					'id' 			=> 'checkbox_remove_help',
					'label'			=> __( 'Remove WP default help', 'simple-custom-admin' ),
					'description'	=> __( 'This will remove the dropdown WP help', 'simple-custom-admin' ),
					'type'			=> 'checkbox',
					'default'		=> '',
				),
				array(
					'id' 			=> 'admin_footer',
					'label'			=> __( 'Footer text' , 'simple-custom-admin' ),
					'description'	=> __( 'This accepts html.', 'simple-custom-admin' ),
					'type'			=> 'textarea',
					'default'		=> '',
					'placeholder'	=> __( 'Change to remove current footer and WP version ', 'simple-custom-admin' ),
				),
			),
		);

		$settings['management'] = array(
			'title'					=> __( 'Management', 'simple-custom-admin' ),
			'description'			=> __( 'Settings associated to another user role that is an alternative to the Administrator', 'simple-custom-admin' ),
			'fields'				=> array(
				array(
					'id' 			=> 'checkbox_disable_posts',
					'label'			=> __( 'Remove Access to Posts', 'simple-custom-admin' ),
					'description'	=> __( 'This will remove the Blog Posting access', 'simple-custom-admin' ),
					'type'			=> 'checkbox',
					'default'		=> '',
				),
				array(
					'id' 			=> 'checkbox_disable_pages',
					'label'			=> __( 'Remove Access to edit Pages', 'simple-custom-admin' ),
					'description'	=> __( 'This will remove the edit Pages access', 'simple-custom-admin' ),
					'type'			=> 'checkbox',
					'default'		=> '',
				),
				array(
					'id' 			=> 'checkbox_disable_media',
					'label'			=> __( 'Remove Access to Media', 'simple-custom-admin' ),
					'description'	=> __( 'This will remove the Media library uploading, editing ang access', 'simple-custom-admin' ),
					'type'			=> 'checkbox',
					'default'		=> '',
				),
				array(
					'id' 			=> 'checkbox_disable_comments',
					'label'			=> __( 'Remove Access to Comments', 'simple-custom-admin' ),
					'description'	=> __( 'This will remove the Commenting access', 'simple-custom-admin' ),
					'type'			=> 'checkbox',
					'default'		=> '',
				),

				array(
					'id' 			=> 'checkbox_settings',
					'label'			=> __( 'Remove Settings Access', 'simple-custom-admin' ),
					'description'	=> __( 'This will remove the Settings access', 'simple-custom-admin' ),
					'type'			=> 'checkbox',
					'default'		=> '',
				),
				array(
					'id' 			=> 'checkbox_user_edit',
					'label'			=> __( 'Remove User Management', 'simple-custom-admin' ),
					'description'	=> __( 'This will remove user management', 'simple-custom-admin' ),
					'type'			=> 'checkbox',
					'default'		=> '',
				),
				array(
					'id' 			=> 'checkbox_plugin_edit',
					'label'			=> __( 'Remove Plugin Management', 'simple-custom-admin' ),
					'description'	=> __( 'This will remove plugin management', 'simple-custom-admin' ),
					'type'			=> 'checkbox',
					'default'		=> '',
				),
				array(
					'id' 			=> 'checkbox_theme_edit',
					'label'			=> __( 'Remove Theme Management', 'simple-custom-admin' ),
					'description'	=> __( 'This will remove theme editing and management', 'simple-custom-admin' ),
					'type'			=> 'checkbox',
					'default'		=> '',
				),
				array(
					'id' 			=> 'checkbox_update_core',
					'label'			=> __( 'Remove Core Update Capability', 'simple-custom-admin' ),
					'description'	=> __( 'This will remove file editing, core updates and plugin updates capability', 'simple-custom-admin' ),
					'type'			=> 'checkbox',
					'default'		=> '',
				),
				array(
					'id' 			=> 'user_role_name',
					'label'			=> __( 'User Role Name' , 'simple-custom-admin' ),
					'description'	=> __( 'If you will be changing the name of the user role, make sure there are no more assigned users on this role', 'simple-custom-admin' ),
					'type'			=> 'text',
					'default'		=> 'manager',
					'placeholder'	=> __( 'Do not leave blank, be careful when changing this value as it my mess up your existing custom roles', 'simple-custom-admin' ),
				),
				array(
					'id' 			=> 'checkbox_remove_tools',
					'label'			=> __( 'Remove Tools', 'simple-custom-admin' ),
					'description'	=> __( 'This will remove Tools', 'simple-custom-admin' ),
					'type'			=> 'checkbox',
					'default'		=> '',
				),
				array(
					'id' 			=> 'reset_checkbox',
					'label'			=> __( 'Reset All to default', 'simple-custom-admin' ),
					'description'	=> __( 'This will delete / reset all changes when plugin is deleted or deactivated', 'simple-custom-admin' ),
					'type'			=> 'checkbox',
					'default'		=> '',
				),
			),
		);

		$settings['dashboard'] = array(
			'title'					=> __( 'Custom Dashboard', 'simple-custom-admin' ),
			'description'			=> __( 'You can customize this to modify the normal admin dashboard', 'simple-custom-admin' ),
			'fields'				=> array(
				array(
					'id' 			=> 'checkbox_remove_dashboard_widgets',
					'label'			=> __( 'Remove the default dashboard widgets', 'simple-custom-admin' ),
					'description'	=> __( 'This will remove the default widgets', 'simple-custom-admin' ),
					'type'			=> 'checkbox',
					'default'		=> '',
				),
				array(
					'id' 			=> 'dashboard_title_1',
					'label'			=> __( 'Dashboard Title' , 'simple-custom-admin' ),
					'description'	=> __( 'This should contain the widget title', 'simple-custom-admin' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> __( 'Change the title', 'simple-custom-admin' ),
				),
				array(
					'id' 			=> 'dashboard_content_1',
					'label'			=> __( 'Custom Dashboard widget contents' , 'simple-custom-admin' ),
					'description'	=> __( 'This accepts html.', 'simple-custom-admin' ),
					'type'			=> 'textarea',
					'default'		=> '',
					'placeholder'	=> __( 'Change to remove current Dashboard widget contents ', 'simple-custom-admin' ),
				),
			),
		);

		$settings = apply_filters( $this->parent->_token . '_settings_fields', $settings );

		return $settings;
	}

	/**
	 * Register plugin settings
	 *
	 * @return void
	 */
	public function register_settings() {
		if ( is_array( $this->settings ) ) {

			// Check posted/selected tab.
			$current_section = '';
			if ( isset( $_POST['tab'] ) && $_POST['tab'] ) {
				$current_section = $_POST['tab'];
			} else {
				if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
					$current_section = $_GET['tab'];
				}
			}

			foreach ( $this->settings as $section => $data ) {

				if ( $current_section && $current_section !== $section ) {
					continue;
				}
				// Add section to page.
				add_settings_section( $section, $data['title'], array( $this, 'settings_section' ), $this->parent->_token . '_settings' );

				foreach ( $data['fields'] as $field ) {

					// Validation callback for field.
					$validation = '';
					if ( isset( $field['callback'] ) ) {
						$validation = $field['callback'];
					}

					// Register field.
					$option_name = $this->base . $field['id'];
					register_setting( $this->parent->_token . '_settings', $option_name, $validation );

					// Add field to page.
					add_settings_field( $field['id'], $field['label'], array( $this->parent->admin, 'display_field' ), $this->parent->_token . '_settings', $section, array( 'field' => $field, 'prefix' => $this->base ) );
				}

				if ( ! $current_section ) {
					break;
				}
			}
		}
	}

	/**
	 * This contains the description of the section.
	 *
	 * @param	string $section	Contains the description.
	 * @return 	void
	 */
	public function settings_section( $section ) {
		$html = '<p> ' . $this->settings[ $section['id'] ]['description'] . '</p>' . "\n";
		echo $html;
	}

	/**
	 * Load settings page content
	 *
	 * @return void
	 */
	public function settings_page() {
		if ( current_user_can( 'create_users' ) ) {

		// Build page HTML.
		$html = '<div class="wrap" id="' . $this->parent->_token . '_settings">' . "\n";
		$html .= '<h2>' . __( 'Simple Custom Admin Settings' , 'simple-custom-admin' ) . '</h2>' . "\n";
		$tab = '';
		if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
			$tab .= $_GET['tab'];
		}

		// Show page tabs.
		if ( is_array( $this->settings ) && 1 < count( $this->settings ) ) {
			$html .= '<h2 class="nav-tab-wrapper">' . "\n";
			$c = 0;
			foreach ( $this->settings as $section => $data ) {
				$class = 'nav-tab'; // Set tab class.
				if ( ! isset( $_GET['tab'] ) ) {
					if ( 0 === $c ) {
						$class .= ' nav-tab-active';
					}
				} else {
					if ( isset( $_GET['tab'] ) && $section === $_GET['tab'] ) {
						$class .= ' nav-tab-active';
					}
				}
				$tab_link = add_query_arg( array( 'tab' => $section ) ); // Set tab link.
				if ( isset( $_GET['settings-updated'] ) ) {
					$tab_link = remove_query_arg( 'settings-updated', $tab_link );
				}
				// Output tab.
				$html .= '<a href="' . $tab_link . '" class="' . esc_attr( $class ) . '">' . esc_html( $data['title'] ) . '</a>' . "\n";
				++$c;
			}
			$html .= '</h2>' . "\n";
		}
			$html .= '<form method="post" action="options.php" enctype="multipart/form-data">' . "\n";
				// Get settings fields.
				ob_start();
				settings_fields( $this->parent->_token . '_settings' );
				do_settings_sections( $this->parent->_token . '_settings' );
				$html .= ob_get_clean();
				$html .= '<p class="submit">' . "\n";
					$html .= '<input type="hidden" name="tab" value="' . esc_attr( $tab ) . '" />' . "\n";
					$html .= '<input name="Submit" type="submit" class="button-primary" value="' . esc_attr( __( 'Save Settings' , 'simple-custom-admin' ) ) . '" />' . "\n";
				$html .= '</p>' . "\n";
			$html .= '</form>' . "\n";
		$html .= '</div>' . "\n";

		echo $html;
	} else {
		echo 'no access';
	}
	}

	/**
	 * Main Simple_Custom_Admin_Settings Instance
	 *
	 * Ensures only one instance of Simple_Custom_Admin_Settings is loaded or can be loaded.
	 *
	 * @since 	1.0.0
	 * @static
	 * @see 	Simple_Custom_Admin()
	 * @param	object $parent Parent of the instance.
	 * @return	Main Simple_Custom_Admin_Settings instance
	 */
	public static function instance( $parent ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $parent );
		}
		return self::$_instance;
	} // End instance()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, esc_html_e( 'Cheatin&#8217; huh?' ) );
	} // End __clone()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html_e( 'Cheatin&#8217; huh?' ) );
	} // End __wakeup()

}
