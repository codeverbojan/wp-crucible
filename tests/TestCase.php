<?php
/**
 * Base test case with Brain\Monkey integration.
 *
 * @package StarterPlugin\Tests
 */

declare( strict_types=1 );

namespace StarterPlugin\Tests;

use Yoast\PHPUnitPolyfills\TestCases\TestCase as PolyfillTestCase;
use Brain\Monkey;

/**
 * Base test case for all plugin unit tests.
 *
 * Sets up and tears down Brain\Monkey for WordPress function mocking.
 */
abstract class TestCase extends PolyfillTestCase {

	/**
	 * Set up Brain\Monkey before each test.
	 *
	 * @return void
	 */
	protected function set_up(): void {
		parent::set_up();
		Monkey\setUp();
	}

	/**
	 * Tear down Brain\Monkey after each test.
	 *
	 * @return void
	 */
	protected function tear_down(): void {
		$this->addToAssertionCount( \Mockery::getContainer()->mockery_getExpectationCount() );
		Monkey\tearDown();
		parent::tear_down();
	}
}
