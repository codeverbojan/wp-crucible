<?php
/**
 * Plugin bootstrap.
 *
 * @package StarterPlugin
 */

declare( strict_types=1 );

namespace StarterPlugin;

defined( 'ABSPATH' ) || exit;

/**
 * Main plugin class.
 *
 * Boots the plugin via a static factory method. The instance is stored in
 * $GLOBALS['starter_plugin'] by the main plugin file.
 */
class Plugin {

	/**
	 * Whether the plugin has been booted.
	 *
	 * @var bool
	 */
	private static bool $booted = false;

	/**
	 * The booted instance.
	 *
	 * @var self|null
	 */
	private static ?self $instance = null;

	/**
	 * Private constructor — use boot() to instantiate.
	 */
	private function __construct() {
	}

	/**
	 * Boot the plugin.
	 *
	 * Returns the existing instance if already booted.
	 *
	 * @return self
	 */
	public static function boot(): self {
		if ( self::$booted && self::$instance instanceof self ) {
			return self::$instance;
		}

		self::$instance = new self();
		self::$instance->register_hooks();
		self::$booted = true;

		return self::$instance;
	}

	/**
	 * Register all plugin hooks.
	 *
	 * @return void
	 */
	private function register_hooks(): void {
		register_activation_hook( STARTER_PLUGIN_FILE, [ $this, 'activate' ] );
		register_deactivation_hook( STARTER_PLUGIN_FILE, [ $this, 'deactivate' ] );

		// --- Register additional feature hooks below this line ---
	}

	/**
	 * Plugin activation callback.
	 *
	 * Populates default options using add_option() which only writes if
	 * the option does not already exist — safe for reactivation.
	 *
	 * @return void
	 */
	public function activate(): void {
		add_option( 'starter_plugin_version', STARTER_PLUGIN_VERSION );
	}

	/**
	 * Plugin deactivation callback.
	 *
	 * Clears scheduled cron events. Does NOT delete data — that happens
	 * in uninstall.php when the user deletes the plugin.
	 *
	 * @return void
	 */
	public function deactivate(): void {
		wp_unschedule_hook( 'starter_plugin_daily_cleanup' );
	}

	/**
	 * Reset boot state for testing.
	 *
	 * @internal Only for use in unit tests.
	 *
	 * @return void
	 */
	public static function reset(): void {
		self::$booted   = false;
		self::$instance = null;
	}
}
