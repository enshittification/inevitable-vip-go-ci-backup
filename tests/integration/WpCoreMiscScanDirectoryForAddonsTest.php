<?php
/**
 * Test vipgoci_wpcore_misc_scan_directory_for_addons() function.
 *
 * @package Automattic/vip-go-ci
 */

declare(strict_types=1);

namespace Vipgoci\Tests\Integration;

use PHPUnit\Framework\TestCase;

/**
 * Class that implements the testing.
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
final class WpCoreMiscScanDirectoryForAddonsTest extends TestCase {
	/**
	 * Temporary directory.
	 *
	 * @var $temp_dir
	 */
	private $temp_dir = '';

	/**
	 * Setup function. Require files.
	 *
	 * @return void
	 */
	protected function setUp() :void {
		require_once __DIR__ . '/IncludesForTests.php';

		$this->temp_dir =
			sys_get_temp_dir() .
			'/directory_for_addons-' .
			hash( 'sha256', random_bytes( 2048 ) );

		if ( true !== mkdir( $this->temp_dir ) ) {
			echo 'Unable to create temporary directory.' . PHP_EOL;

			$this->temp_dir = '';
		}
	}

	/**
	 * Tear down function. Clean up temporary files.
	 *
	 * @return void
	 */
	protected function tearDown() :void {
		if ( ! empty( $this->temp_dir ) ) {
			if ( false === exec(
				escapeshellcmd( 'rm' ) .
				' -rf ' .
				escapeshellarg( $this->temp_dir )
			) ) {
				echo 'Unable to remove temporary directory' . PHP_EOL;

				return;
			}
		}
	}

	/**
	 * Check if function detects plugins and themes. Scans subdirectories.
	 *
	 * @covers ::vipgoci_wpcore_misc_scan_directory_for_addons
	 *
	 * @return void
	 */
	public function testWpcoreMiscScanDirectoryForAdddonsScanSubdirectories(): void {
		if ( empty( $this->temp_dir ) ) {
			$this->markTestSkipped(
				'Temporary directory not existing.'
			);

			return;
		}

		$cp_cmd = escapeshellcmd( 'cp' ) .
			' -R ' .
			escapeshellarg( __DIR__ . '/helper-files/WpCoreMiscScanDirectoryForAdddonsTest' ) .
			' ' .
			escapeshellarg( $this->temp_dir );

		if ( false === exec( $cp_cmd ) ) {
			$this->markTestSkipped(
				'Unable to extract tar file'
			);

			return;
		}

		$results_expected = array(
			'vipgoci-addon-theme-addon2'                => array(
				'type'             => 'vipgoci-addon-theme',
				'addon_headers'    => array(
					'Name'        => 'My Package',
					'ThemeURI'    => 'http://wordpress.org/test/my-package/',
					'Description' => 'My text.',
					'Author'      => 'Author Name',
					'AuthorURI'   => 'http://wordpress.org/author/test123',
					'Version'     => '1.0.0',
					'Template'    => '',
					'Status'      => '',
					'TextDomain'  => '',
					'DomainPath'  => '',
					'RequiresWP'  => '',
					'RequiresPHP' => '',
					'UpdateURI'   => '',
					'Title'       => 'My Package',
					'AuthorName'  => 'Author Name',
				),
				'name'             => 'My Package',
				'version_detected' => '1.0.0',
				'file_name'        => $this->temp_dir . '/WpCoreMiscScanDirectoryForAdddonsTest/mu-plugins/addon2/style.css',
			),
			'vipgoci-addon-plugin-addon1/file2.php'     => array(
				'type'             => 'vipgoci-addon-plugin',
				'addon_headers'    => array(
					'Name'        => 'My <h1>Other</h1> Package',
					'PluginURI'   => 'http://wordpress.org/test/my-other-package/',
					'Version'     => '1.1.0',
					'Description' => 'My text.',
					'Author'      => 'Author Name',
					'AuthorURI'   => 'http://wordpress.org/author/test123',
					'TextDomain'  => '',
					'DomainPath'  => '',
					'Network'     => '',
					'RequiresWP'  => '',
					'RequiresPHP' => '',
					'UpdateURI'   => '',
					'Title'       => 'My <h1>Other</h1> Package',
					'AuthorName'  => 'Author Name',
				),
				'name'             => 'My <h1>Other</h1> Package',
				'version_detected' => '1.1.0',
				'file_name'        => $this->temp_dir . '/WpCoreMiscScanDirectoryForAdddonsTest/mu-plugins/addon1/file2.php',
			),
			'vipgoci-addon-plugin-this-is-a-plugin.php' => array(
				'type'             => 'vipgoci-addon-plugin',
				'addon_headers'    => array(
					'Name'        => 'This is a plugin.',
					'PluginURI'   => 'http://wordpress.org/test/my-other-package/',
					'Version'     => '15.1.0',
					'Description' => 'This is indeed <b>a plugin</b>..',
					'Author'      => 'Test author.',
					'AuthorURI'   => 'http://wordpress.org/author/test124',
					'TextDomain'  => '',
					'DomainPath'  => '',
					'Network'     => '',
					'RequiresWP'  => '',
					'RequiresPHP' => '',
					'UpdateURI'   => '',
					'Title'       => 'This is a plugin.',
					'AuthorName'  => 'Test author.',
				),
				'name'             => 'This is a plugin.',
				'version_detected' => '15.1.0',
				'file_name'        => $this->temp_dir . '/WpCoreMiscScanDirectoryForAdddonsTest/mu-plugins/this-is-a-plugin.php',
			),
		);

		vipgoci_unittests_output_suppress();

		$results_actual = vipgoci_wpcore_misc_scan_directory_for_addons(
			$this->temp_dir . '/WpCoreMiscScanDirectoryForAdddonsTest',
			'mu-plugins',
			array( 'php' ),
			array( 'css' ),
			true
		);

		vipgoci_unittests_output_unsuppress();

		/*
		 * Different systems will return files in different
		 * order; use assertEquals() to avoid failures due to this.
		 */
		$this->assertEquals(
			$results_expected,
			$results_actual
		);
	}

	/**
	 * Check if function detects plugins and themes. Does not scan
	 * subdirectories.
	 *
	 * @covers ::vipgoci_wpcore_misc_scan_directory_for_addons
	 *
	 * @return void
	 */
	public function testWpcoreMiscScanDirectoryForAdddonsSkipSubdirectories(): void {
		if ( empty( $this->temp_dir ) ) {
			$this->markTestSkipped(
				'Temporary directory not existing.'
			);

			return;
		}

		$cp_cmd = escapeshellcmd( 'cp' ) .
			' -R ' .
			escapeshellarg( __DIR__ . '/helper-files/WpCoreMiscScanDirectoryForAdddonsTest' ) .
			' ' .
			escapeshellarg( $this->temp_dir );

		if ( false === exec( $cp_cmd ) ) {
			$this->markTestSkipped(
				'Unable to extract tar file'
			);

			return;
		}

		$results_expected = array(
			'vipgoci-addon-plugin-this-is-a-plugin.php' => array(
				'type'             => 'vipgoci-addon-plugin',
				'addon_headers'    => array(
					'Name'        => 'This is a plugin.',
					'PluginURI'   => 'http://wordpress.org/test/my-other-package/',
					'Version'     => '15.1.0',
					'Description' => 'This is indeed <b>a plugin</b>..',
					'Author'      => 'Test author.',
					'AuthorURI'   => 'http://wordpress.org/author/test124',
					'TextDomain'  => '',
					'DomainPath'  => '',
					'Network'     => '',
					'RequiresWP'  => '',
					'RequiresPHP' => '',
					'UpdateURI'   => '',
					'Title'       => 'This is a plugin.',
					'AuthorName'  => 'Test author.',
				),
				'name'             => 'This is a plugin.',
				'version_detected' => '15.1.0',
				'file_name'        => $this->temp_dir . '/WpCoreMiscScanDirectoryForAdddonsTest/mu-plugins/this-is-a-plugin.php',
			),
		);

		vipgoci_unittests_output_suppress();

		$results_actual = vipgoci_wpcore_misc_scan_directory_for_addons(
			$this->temp_dir . '/WpCoreMiscScanDirectoryForAdddonsTest',
			'mu-plugins',
			array( 'php' ),
			array( 'css' ),
			false
		);

		vipgoci_unittests_output_unsuppress();

		/*
		 * Different systems will return files in different
		 * order; use assertEquals() to avoid failures due to this.
		 */
		$this->assertEquals(
			$results_expected,
			$results_actual
		);
	}

}

