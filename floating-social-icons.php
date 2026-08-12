<?php
/**
 * Plugin Name:       Floating Social Media Icons
 * Plugin URI:        https://github.com/Verisimilitudesque/WordPress-Floating-Social-Media-Icons
 * Description:       Displays a customizable floating bar of social media icons on the front end. Configure which side it floats on, its distance from the top, colors, sizing, and which networks to show from the admin settings page.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Jon Schear
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       floating-social-icons
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'FSI_VERSION', '1.0.0' );
define( 'FSI_PLUGIN_FILE', __FILE__ );
define( 'FSI_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'FSI_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'FSI_OPTION_NAME', 'fsi_settings' );

require_once FSI_PLUGIN_DIR . 'includes/class-fsi-icons.php';
require_once FSI_PLUGIN_DIR . 'includes/class-fsi-settings.php';
require_once FSI_PLUGIN_DIR . 'includes/class-fsi-frontend.php';

/**
 * Default plugin settings.
 *
 * @return array
 */
function fsi_get_default_settings() {
	return array(
		'position'          => 'right',
		'top_offset'        => 200,
		'icon_size'         => 44,
		'shape'             => 'circle',
		'icon_color'        => '#ffffff',
		'bg_color'          => '#333333',
		'hover_color'       => '#0073aa',
		'icon_spacing'      => 8,
		'hide_on_mobile'    => false,
		'mobile_breakpoint' => 768,
		'icons'             => array(),
	);
}

/**
 * Reads the saved settings, merged with defaults for any missing keys.
 *
 * @return array
 */
function fsi_get_settings() {
	$saved = get_option( FSI_OPTION_NAME, array() );

	if ( ! is_array( $saved ) ) {
		$saved = array();
	}

	return wp_parse_args( $saved, fsi_get_default_settings() );
}

/**
 * Seeds the default option on activation, without overwriting existing settings.
 */
function fsi_activate() {
	if ( false === get_option( FSI_OPTION_NAME ) ) {
		add_option( FSI_OPTION_NAME, fsi_get_default_settings() );
	}
}
register_activation_hook( __FILE__, 'fsi_activate' );

/**
 * Loads the plugin translation files.
 */
function fsi_load_textdomain() {
	load_plugin_textdomain( 'floating-social-icons', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'fsi_load_textdomain' );

new FSI_Settings();
new FSI_Frontend();
