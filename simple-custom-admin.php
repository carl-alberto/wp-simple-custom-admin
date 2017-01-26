<?php
/**
 * Plugin Name: Simple Custom Admin
 * Version: 1.2
 * Plugin URI: https://profiles.wordpress.org/carl-alberto#content-plugins
 * Description: Simple plugin to customize or white label the WP admin dashboard for non Administrator accounts.
 * Author: Carl Alberto
 * Author URI: https://carl.alber2.com/
 * Requires at least: 4.0
 * Tested up to: 4.0
 *
 * Text Domain: simple-custom-admin
 * Domain Path: /lang/
 *
 * @package Simple Custom Admin
 * @author Carl Alberto
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load plugin class files.
require_once( 'includes/class-simple-custom-admin.php' );
require_once( 'includes/class-simple-custom-admin-settings.php' );

// Load plugin libraries.
require_once( 'includes/lib/class-simple-custom-admin-admin-api.php' );

// Load custom admin roles.
require_once( 'includes/class-admin-roles.php' );
/**
 * Returns the main instance of Simple_Custom_Admin to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object Simple_Custom_Admin
 */
function simple_custom_admin() {
	$instance = Simple_Custom_Admin::instance( __FILE__, '1.1' );

	if ( is_null( $instance->settings ) ) {
		$instance->settings = Simple_Custom_Admin_Settings::instance( $instance );
	}

	return $instance;
}

Simple_Custom_Admin();
