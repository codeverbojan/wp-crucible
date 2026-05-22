<?php
/**
 * Tests for the admin settings page.
 *
 * @package StarterPlugin\Tests\Unit\Admin
 */

declare( strict_types=1 );

namespace StarterPlugin\Tests\Unit\Admin;

use StarterPlugin\Admin\SettingsPage;
use StarterPlugin\Tests\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers \StarterPlugin\Admin\SettingsPage
 */
class SettingsPageTest extends TestCase {

	/**
	 * @test
	 */
	public function defaults_returns_expected_keys(): void {
		$defaults = SettingsPage::defaults();

		$this->assertArrayHasKey( 'example_text', $defaults );
		$this->assertArrayHasKey( 'example_checkbox', $defaults );
		$this->assertSame( '', $defaults['example_text'] );
		$this->assertSame( 0, $defaults['example_checkbox'] );
	}

	/**
	 * @test
	 */
	public function sanitize_options_sanitizes_text_field(): void {
		Functions\when( 'wp_unslash' )->returnArg();
		Functions\when( 'sanitize_text_field' )->alias(
			static function ( $str ) {
				return trim( strip_tags( (string) $str ) );
			}
		);
		Functions\when( 'absint' )->alias(
			static function ( $value ) {
				return abs( (int) $value );
			}
		);

		$page   = new SettingsPage();
		$result = $page->sanitize_options(
			[
				'example_text'     => '<script>alert("xss")</script>Hello',
				'example_checkbox' => '1',
			]
		);

		$this->assertSame( 'alert("xss")Hello', $result['example_text'] );
		$this->assertSame( 1, $result['example_checkbox'] );
	}

	/**
	 * @test
	 */
	public function sanitize_options_handles_missing_keys(): void {
		Functions\when( 'wp_unslash' )->returnArg();
		Functions\when( 'sanitize_text_field' )->returnArg();
		Functions\when( 'absint' )->alias(
			static function ( $value ) {
				return abs( (int) $value );
			}
		);

		$page   = new SettingsPage();
		$result = $page->sanitize_options( [] );

		$this->assertSame( '', $result['example_text'] );
		$this->assertSame( 0, $result['example_checkbox'] );
	}

	/**
	 * @test
	 */
	public function sanitize_options_handles_non_array_input(): void {
		Functions\when( 'wp_unslash' )->returnArg();
		Functions\when( 'sanitize_text_field' )->returnArg();
		Functions\when( 'absint' )->alias(
			static function ( $value ) {
				return abs( (int) $value );
			}
		);

		$page   = new SettingsPage();
		$result = $page->sanitize_options( 'not-an-array' );

		$this->assertIsArray( $result );
		$this->assertSame( '', $result['example_text'] );
		$this->assertSame( 0, $result['example_checkbox'] );
	}

	/**
	 * @test
	 */
	public function sanitize_options_checkbox_uses_absint(): void {
		Functions\when( 'wp_unslash' )->returnArg();
		Functions\when( 'sanitize_text_field' )->returnArg();
		Functions\when( 'absint' )->alias(
			static function ( $value ) {
				return abs( (int) $value );
			}
		);

		$page   = new SettingsPage();
		$result = $page->sanitize_options( [ 'example_checkbox' => '-5' ] );

		$this->assertSame( 5, $result['example_checkbox'] );
	}

	/**
	 * @test
	 */
	public function add_settings_link_prepends_link(): void {
		Functions\when( 'admin_url' )->justReturn( 'https://example.com/wp-admin/options-general.php?page=starter-plugin' );
		Functions\when( 'esc_url' )->returnArg();
		Functions\when( 'esc_html__' )->returnArg();

		$page  = new SettingsPage();
		$links = $page->add_settings_link( [ 'deactivate' ] );

		$this->assertCount( 2, $links );
		$this->assertStringContainsString( 'options-general.php?page=starter-plugin', $links[0] );
		$this->assertSame( 'deactivate', $links[1] );
	}

	/**
	 * @test
	 */
	public function register_hooks_admin_actions(): void {
		Functions\expect( 'add_action' )
			->once()
			->with( 'admin_menu', \Mockery::type( 'array' ) );

		Functions\expect( 'add_action' )
			->once()
			->with( 'admin_init', \Mockery::type( 'array' ) );

		Functions\expect( 'add_filter' )
			->once()
			->with( \Mockery::type( 'string' ), \Mockery::type( 'array' ) );

		Functions\when( 'plugin_basename' )->justReturn( 'starter-plugin/starter-plugin.php' );

		$page = new SettingsPage();
		$page->register();
	}
}
