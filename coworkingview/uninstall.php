<?php
/**
 * Remove the plugin's option and cached responses.
 *
 * @package CoworkingView
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'coworkingview_settings' );

global $wpdb;
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Transients have no API to delete by prefix.
$wpdb->query(
	$wpdb->prepare(
		"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
		$wpdb->esc_like( '_transient_coworkingview_' ) . '%',
		$wpdb->esc_like( '_transient_timeout_coworkingview_' ) . '%'
	)
);
