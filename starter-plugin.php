<?php
/**
 * Starter Plugin
 *
 * @package           StarterPlugin
 * @author            Your Name
 * @copyright         2026 Your Name
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Starter Plugin
 * Plugin URI:        https://example.com/starter-plugin
 * Description:       A starter plugin scaffolded by WP Crucible.
 * Version:           1.0.0
 * Requires at least: 6.4
 * Requires PHP:      8.1
 * Author:            Your Name
 * Author URI:        https://example.com
 * Text Domain:       starter-plugin
 * Domain Path:       /languages
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 *
 * Add plugin dependencies below (WP 6.5+, comma-separated wp.org slugs):
 * Requires Plugins:
 */

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'STARTER_PLUGIN_VERSION' ) ) {
	define( 'STARTER_PLUGIN_VERSION', '1.0.0' );
}
if ( ! defined( 'STARTER_PLUGIN_FILE' ) ) {
	define( 'STARTER_PLUGIN_FILE', __FILE__ );
}
if ( ! defined( 'STARTER_PLUGIN_DIR' ) ) {
	define( 'STARTER_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'STARTER_PLUGIN_URL' ) ) {
	define( 'STARTER_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

/**
 * PSR-4 autoloader for the StarterPlugin namespace.
 *
 * Maps StarterPlugin\ to the src/ directory. Replaces Composer autoloader
 * in production so no vendor/ directory ships with the plugin.
 *
 * @param string $class_name Fully-qualified class name.
 */
spl_autoload_register(
	static function ( string $class_name ): void {
		$prefix = 'StarterPlugin\\';
		$len    = strlen( $prefix );

		if ( strncmp( $class_name, $prefix, $len ) !== 0 ) {
			return;
		}

		$relative = substr( $class_name, $len );

		if ( str_contains( $relative, '..' )
			|| str_contains( $relative, "\0" )
			|| str_starts_with( $relative, '/' )
			|| str_starts_with( $relative, '\\' ) ) {
			return;
		}

		$file = STARTER_PLUGIN_DIR . 'src/' . str_replace( '\\', '/', $relative ) . '.php';

		if ( file_exists( $file ) ) {
			require $file;
		}
	}
);

$GLOBALS['starter_plugin'] = StarterPlugin\Plugin::boot();
