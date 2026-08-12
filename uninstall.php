<?php
/**
 * Uninstall routine for Floating Social Icons.
 *
 * @package Floating_Social_Icons
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'fsi_settings' );
