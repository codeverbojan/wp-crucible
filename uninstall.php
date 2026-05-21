<?php
/**
 * Uninstall handler.
 *
 * Cleans up all plugin data when the plugin is deleted via wp-admin.
 * This file is called by WordPress — do NOT load the main plugin file
 * to avoid triggering hook registration.
 *
 * @package StarterPlugin
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) || ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Keep this list in sync with options created in Plugin::activate()
// and any options registered via register_setting() in Admin/SettingsPage.php.
$starter_plugin_options = [
	'starter_plugin_version',
];

foreach ( $starter_plugin_options as $starter_plugin_option ) {
	delete_option( $starter_plugin_option );
}

// Uncomment when using custom database tables:
// global $wpdb;
// $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}starter_plugin_example" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange

// Clear any scheduled cron events.
wp_unschedule_hook( 'starter_plugin_daily_cleanup' );

// Uncomment for multisite network-wide cleanup:
// if ( is_multisite() ) {
// 	$starter_plugin_sites = get_sites( [ 'fields' => 'ids', 'number' => 0 ] );
// 	foreach ( $starter_plugin_sites as $starter_plugin_site_id ) {
// 		switch_to_blog( $starter_plugin_site_id );
// 		// Run per-site option/transient cleanup here.
// 		restore_current_blog();
// 	}
// }

// Clear transients with plugin prefix.
global $wpdb;
$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$wpdb->prepare(
		"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
		$wpdb->esc_like( '_transient_starter_plugin_' ) . '%',
		$wpdb->esc_like( '_transient_timeout_starter_plugin_' ) . '%'
	)
);
