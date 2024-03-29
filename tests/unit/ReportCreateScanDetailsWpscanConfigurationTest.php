<?php
/**
 * Test vipgoci_report_create_scan_details_wpscan_configuration(),
 * which outputs HTML code.
 *
 * @package Automattic/vip-go-ci
 */

declare(strict_types=1);

namespace Vipgoci\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Class that implements the testing.
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
final class ReportCreateScanDetailsWpscanConfigurationTest extends TestCase {
	/**
	 * Options array.
	 *
	 * @var $options
	 */
	private array $options = array();

	/**
	 * Setup function. Require files, set options variable.
	 *
	 * @return void
	 */
	protected function setUp() :void {
		require_once __DIR__ . '/../../reports.php';
		require_once __DIR__ . '/../../output-security.php';
		require_once __DIR__ . '/../../defines.php';

		$this->options = array();
	}

	/**
	 * Clean up after running.
	 *
	 * @return void
	 */
	protected function tearDown() :void {
		unset( $this->options );
	}

	/**
	 * Test function with most reporting disabled.
	 *
	 * @covers ::vipgoci_report_create_scan_details_wpscan_configuration
	 *
	 * @return void
	 */
	public function testCreateDetails1(): void {
		$this->options['wpscan-api'] = false;

		$actual_output = vipgoci_report_create_scan_details_wpscan_configuration(
			$this->options
		);

		$this->assertStringContainsString(
			'WPScan API configuration',
			$actual_output
		);

		$this->assertStringContainsString(
			'<p>WPScan API scanning enabled: ' . PHP_EOL .
			'<code>false</code></p>',
			$actual_output
		);

		$this->assertStringNotContainsString(
			'WPScan API URL',
			$actual_output
		);

		$this->assertStringNotContainsString(
			'Directories scanned',
			$actual_output
		);

		$this->assertStringNotContainsString(
			'Directories not scanned',
			$actual_output
		);

		$this->assertStringNotContainsString(
			'file extensions',
			$actual_output
		);
	}

	/**
	 * Test function with most reporting disabled.
	 *
	 * @covers ::vipgoci_report_create_scan_details_wpscan_configuration
	 *
	 * @return void
	 */
	public function testCreateDetails2(): void {
		$this->options['wpscan-api']                        = true;
		$this->options['wpscan-api-paths']                  = array( 'plugins', 'themes' );
		$this->options['wpscan-api-skip-folders']           = array( 'skip-dir1', 'skip-dir2' );
		$this->options['wpscan-api-plugin-file-extensions'] = array( 'php' );
		$this->options['wpscan-api-theme-file-extensions']  = array( 'css' );

		$actual_output = vipgoci_report_create_scan_details_wpscan_configuration(
			$this->options
		);

		$this->assertStringContainsString(
			'WPScan API configuration',
			$actual_output
		);

		$this->assertStringContainsString(
			'<p>WPScan API scanning enabled: ' . PHP_EOL .
			'<code>true</code></p>',
			$actual_output
		);

		$this->assertStringContainsString(
			'<p>WPScan API URL: ' . PHP_EOL .
			'<code>https://wpscan.com/api/v3</code></p>',
			$actual_output
		);

		$this->assertStringContainsString(
			'<p>Directories scanned:</p>' . PHP_EOL .
			'<ul>' . PHP_EOL . '<li><code>plugins</code></li><li><code>themes</code></li></ul>',
			$actual_output
		);

		$this->assertStringContainsString(
			'<p>Directories not scanned:</p>' . PHP_EOL .
			'<ul>' . PHP_EOL . '<li><code>skip-dir1</code></li><li><code>skip-dir2</code></li></ul>',
			$actual_output
		);

		$this->assertStringContainsString(
			'<p>Scan added/modified plugins based on headers present in files with file extensions:</p>' . PHP_EOL .
			'<ul>' . PHP_EOL . '<li><code>php</code></li></ul>',
			$actual_output
		);

		$this->assertStringContainsString(
			'<p>Scan added/modified themes based on headers present in files with file extensions:</p>' . PHP_EOL .
			'<ul>' . PHP_EOL . '<li><code>css</code></li></ul>',
			$actual_output
		);
	}
}
