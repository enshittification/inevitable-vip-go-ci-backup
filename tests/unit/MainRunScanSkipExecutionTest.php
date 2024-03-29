<?php
/**
 * Test function vipgoci_run_scan_skip_execution().
 *
 * @package Automattic/vip-go-ci
 */

declare(strict_types=1);

namespace Vipgoci\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Check if skip execution functionality works.
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
final class MainRunScanSkipExecutionTest extends TestCase {
	/**
	 * Options array.
	 *
	 * @var $options
	 */
	private array $options = array();

	/**
	 * Set up variable, indicate what test is running.
	 *
	 * @return void
	 */
	protected function setUp() :void {
		require_once __DIR__ . '/helper/IndicateTestId.php';

		require_once __DIR__ . './../../defines.php';
		require_once __DIR__ . './../../main.php';
		require_once __DIR__ . './../../log.php';

		/*
		 * Indicate that this particular test is running,
		 * needed so that vipgoci_sysexit() can return
		 * instead of exiting. See the function itself.
		 */
		vipgoci_unittests_indicate_test_id( 'MainRunScanSkipExecutionTest' );

		$this->options = array();
	}

	/**
	 * Remove the indication and clear variable.
	 *
	 * @return void
	 */
	protected function tearDown() :void {
		vipgoci_unittests_remove_indication_for_test_id( 'MainRunScanSkipExecutionTest' );

		unset( $this->options );
	}

	/**
	 * Check if vipgoci_run_scan_skip_execution() attempts to
	 * exit.
	 *
	 * @covers ::vipgoci_run_scan_skip_execution
	 *
	 * @return void
	 */
	public function testRunScanSkipExecution() :void {
		$this->options['skip-execution'] = true;

		ob_start();

		vipgoci_run_scan_skip_execution(
			$this->options
		);

		$printed_data = ob_get_contents();

		ob_end_clean();

		/*
		 * Check if expected string was printed
		 */
		$printed_data_found = strpos(
			$printed_data,
			'"Skipping scanning entirely, as determined by configuration";'
		);

		$this->assertNotFalse(
			$printed_data_found
		);
	}

	/**
	 * Check if vipgoci_run_scan_skip_execution() returns
	 * with exit-status.
	 *
	 * @covers ::vipgoci_run_scan_skip_execution
	 *
	 * @return void
	 */
	public function testRunScanDoesNotSkipExecution() :void {
		$this->options['skip-execution'] = false;

		ob_start();

		vipgoci_run_scan_skip_execution(
			$this->options
		);

		$printed_data = ob_get_contents();

		ob_end_clean();

		/*
		 * Check if nothing was printed.
		 */
		$this->assertEmpty(
			$printed_data
		);
	}
}
