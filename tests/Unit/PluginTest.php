<?php
/**
 * Tests for the Plugin bootstrap class.
 *
 * @package StarterPlugin\Tests\Unit
 */

declare( strict_types=1 );

namespace StarterPlugin\Tests\Unit;

use StarterPlugin\Plugin;
use StarterPlugin\Tests\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers \StarterPlugin\Plugin
 */
class PluginTest extends TestCase {

	/**
	 * Reset the singleton before each test.
	 *
	 * @return void
	 */
	protected function set_up(): void {
		parent::set_up();
		Plugin::reset();
	}

	/**
	 * @test
	 */
	public function boot_returns_plugin_instance(): void {
		Functions\when( 'register_activation_hook' )->justReturn();
		Functions\when( 'register_deactivation_hook' )->justReturn();
		Functions\when( 'is_admin' )->justReturn( false );

		$instance = Plugin::boot();

		$this->assertInstanceOf( Plugin::class, $instance );
	}

	/**
	 * @test
	 */
	public function boot_returns_same_instance_on_second_call(): void {
		Functions\when( 'register_activation_hook' )->justReturn();
		Functions\when( 'register_deactivation_hook' )->justReturn();
		Functions\when( 'is_admin' )->justReturn( false );

		$first  = Plugin::boot();
		$second = Plugin::boot();

		$this->assertSame( $first, $second );
	}

	/**
	 * @test
	 */
	public function boot_registers_activation_hook(): void {
		Functions\expect( 'register_activation_hook' )
			->once()
			->with( STARTER_PLUGIN_FILE, \Mockery::type( 'array' ) );

		Functions\when( 'register_deactivation_hook' )->justReturn();
		Functions\when( 'is_admin' )->justReturn( false );

		Plugin::boot();
	}

	/**
	 * @test
	 */
	public function boot_registers_deactivation_hook(): void {
		Functions\when( 'register_activation_hook' )->justReturn();

		Functions\expect( 'register_deactivation_hook' )
			->once()
			->with( STARTER_PLUGIN_FILE, \Mockery::type( 'array' ) );

		Functions\when( 'is_admin' )->justReturn( false );

		Plugin::boot();
	}

	/**
	 * @test
	 */
	public function activate_adds_version_option(): void {
		Functions\expect( 'add_option' )
			->once()
			->with( 'starter_plugin_version', STARTER_PLUGIN_VERSION );

		Functions\when( 'register_activation_hook' )->justReturn();
		Functions\when( 'register_deactivation_hook' )->justReturn();
		Functions\when( 'is_admin' )->justReturn( false );

		$plugin = Plugin::boot();
		$plugin->activate();
	}

	/**
	 * @test
	 */
	public function deactivate_unschedules_cron_hook(): void {
		Functions\expect( 'wp_unschedule_hook' )
			->once()
			->with( 'starter_plugin_daily_cleanup' );

		Functions\when( 'register_activation_hook' )->justReturn();
		Functions\when( 'register_deactivation_hook' )->justReturn();
		Functions\when( 'is_admin' )->justReturn( false );

		$plugin = Plugin::boot();
		$plugin->deactivate();
	}

	/**
	 * @test
	 */
	public function reset_allows_fresh_boot(): void {
		Functions\when( 'register_activation_hook' )->justReturn();
		Functions\when( 'register_deactivation_hook' )->justReturn();
		Functions\when( 'is_admin' )->justReturn( false );

		$first = Plugin::boot();
		Plugin::reset();
		$second = Plugin::boot();

		$this->assertNotSame( $first, $second );
	}
}
