<?php
/**
 * PHPUnit bootstrap file.
 *
 * @package StarterPlugin\Tests
 */

declare( strict_types=1 );

// WordPress guard — source files check this before loading.
define( 'ABSPATH', dirname( __DIR__ ) . '/' );

// Plugin constants — mirror what starter-plugin.php defines at runtime.
define( 'STARTER_PLUGIN_VERSION', '1.0.0' );
define( 'STARTER_PLUGIN_FILE', dirname( __DIR__ ) . '/starter-plugin.php' );
define( 'STARTER_PLUGIN_DIR', dirname( __DIR__ ) . '/' );
define( 'STARTER_PLUGIN_URL', 'https://example.com/wp-content/plugins/starter-plugin/' );

// Composer autoloader.
require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// Minimal $wpdb stub — prevents fatal errors when production code references $wpdb.
// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
$GLOBALS['wpdb'] = new class() {
	/** @var string */
	public $options = 'wp_options';
	/** @var string */
	public $prefix = 'wp_';
};
