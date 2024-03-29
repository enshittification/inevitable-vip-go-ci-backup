<?php
/**
 * Test vipgoci_wpscan_report_comment_format_result() function.
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
final class WpscanReportCommentFormatResultTest extends TestCase {
	/**
	 * Setup function. Require files.
	 *
	 * @return void
	 */
	protected function setUp() :void {
		require_once __DIR__ . '/../../defines.php';
		require_once __DIR__ . '/../../output-security.php';
		require_once __DIR__ . '/../../github-misc.php';
		require_once __DIR__ . '/../../wpscan-reports.php';
		require_once __DIR__ . '/../../log.php';
		require_once __DIR__ . '/../../other-web-services.php';

		require_once __DIR__ . '/../integration/IncludesForTestsOutputControl.php';
		require_once __DIR__ . '/helper/IndicateTestId.php';
	}

	/**
	 * Test common usage when reporting results for a plugin.
	 *
	 * @covers ::vipgoci_wpscan_report_comment_format_result
	 *
	 * @return void
	 */
	public function testReportResultPlugin(): void {
		vipgoci_unittests_output_suppress();

		$report_str = vipgoci_wpscan_report_comment_format_result(
			'repo_owner',
			'repo_name',
			'commit12345id',
			array(
				'security' => VIPGOCI_WPSCAN_OBSOLETE,
				'message'  => 'My Plugin #',
				'details'  => array(
					'slug'                => 'my-plugin',
					'url'                 => 'https://wordpress.org/plugins/my-plugin',
					'installed_location'  => 'plugins/my-plugin',
					'version_detected'    => '1.0.0',
					'latest_version'      => '2.0.0',
					'latest_download_uri' => 'https://downloads.wordpress.org/plugins/my-plugin-2.0.0.zip',
					'vulnerabilities'     => array(
						array(
							'id'    => '0100100 ;',
							'title' => 'Security problem in My Plugin < 1.9.0',
							'cvss'  => array(
								'score' => '7.3',
							),
						),
					),
				),
			),
			VIPGOCI_ADDON_PLUGIN,
			array(
				10,
				20,
			)
		);

		vipgoci_unittests_output_unsuppress();

		$this->assertStringContainsString(
			'Plugin with update available',
			$report_str
		);

		$this->assertStringNotContainsString(
			'with known vulnerability',
			$report_str
		);

		$this->assertStringNotContainsString(
			'theme',
			$report_str
		);

		$this->assertStringContainsString(
			'Plugin name',
			$report_str
		);

		$this->assertStringContainsString(
			'My Plugin \#',
			$report_str
		);

		$this->assertStringContainsString(
			'Plugin URI',
			$report_str
		);

		$this->assertStringContainsString(
			'https://wordpress.org/plugins/my-plugin',
			$report_str
		);

		$this->assertStringContainsString(
			'Installed location',
			$report_str
		);

		$this->assertStringContainsString(
			VIPGOCI_GITHUB_WEB_BASE_URL . '/repo_owner/repo_name/tree/commit12345id/plugins/my-plugin',
			$report_str
		);

		$this->assertStringContainsString(
			'plugins/my-plugin',
			$report_str
		);

		$this->assertStringContainsString(
			'Version observed',
			$report_str
		);

		$this->assertStringContainsString(
			'1.0.0',
			$report_str
		);

		$this->assertStringContainsString(
			'Latest version available',
			$report_str
		);

		$this->assertStringContainsString(
			'2.0.0',
			$report_str
		);

		$this->assertStringContainsString(
			'Latest version download URI',
			$report_str
		);

		$this->assertStringContainsString(
			'https://downloads.wordpress.org/plugins/my-plugin-2.0.0.zip',
			$report_str
		);

		$this->assertStringContainsString(
			'Title',
			$report_str
		);

		$this->assertStringContainsString(
			'Security problem in My Plugin &#60; 1\.9\.0',
			$report_str
		);

		$this->assertStringContainsString(
			'Details',
			$report_str
		);

		$this->assertStringContainsString(
			VIPGOCI_WPSCAN_BASE_URL . '/vulnerability/0100100%20%3B',
			$report_str
		);

		$this->assertStringContainsString(
			'Severity',
			$report_str
		);

		$this->assertStringContainsString(
			'7.3/10 (HIGH)',
			$report_str
		);
	}

	/**
	 * Test common usage when reporting results for a theme.
	 *
	 * @covers ::vipgoci_wpscan_report_comment_format_result
	 *
	 * @return void
	 */
	public function testReportResultTheme(): void {
		vipgoci_unittests_output_suppress();

		$report_str = vipgoci_wpscan_report_comment_format_result(
			'repo_owner',
			'repo_name',
			'commit12345id',
			array(
				'security' => VIPGOCI_WPSCAN_VULNERABLE,
				'message'  => 'My Theme',
				'details'  => array(
					'slug'                => 'my-plugin',
					'url'                 => 'https://wordpress.org/themes/my-theme',
					'installed_location'  => 'themes/my-theme',
					'version_detected'    => '1.0.0',
					'latest_version'      => '2.0.0',
					'latest_download_uri' => 'https://downloads.wordpress.org/themes/my-theme-2.0.0.zip',
					'vulnerabilities'     => array(
						array(
							'id'    => '0100100',
							'title' => 'Security problem in My Theme',
							'cvss'  => array(
								'score' => '5.0',
							),
						),
					),
				),
			),
			VIPGOCI_ADDON_THEME,
			array(
				30,
				40,
			)
		);

		vipgoci_unittests_output_unsuppress();

		$this->assertStringContainsString(
			'Theme with known vulnerability',
			$report_str
		);

		$this->assertStringNotContainsString(
			'with update available',
			$report_str
		);

		$this->assertStringNotContainsString(
			'Plugin',
			$report_str
		);

		$this->assertStringContainsString(
			'Theme name',
			$report_str
		);

		$this->assertStringContainsString(
			'My Theme',
			$report_str
		);

		$this->assertStringContainsString(
			'Theme URI',
			$report_str
		);

		$this->assertStringContainsString(
			'https://wordpress.org/themes/my-theme',
			$report_str
		);

		$this->assertStringContainsString(
			'Installed location',
			$report_str
		);

		$this->assertStringContainsString(
			VIPGOCI_GITHUB_WEB_BASE_URL . '/repo_owner/repo_name/tree/commit12345id/themes/my-theme',
			$report_str
		);

		$this->assertStringContainsString(
			'themes/my-theme',
			$report_str
		);

		$this->assertStringContainsString(
			'Version observed',
			$report_str
		);

		$this->assertStringContainsString(
			'1.0.0',
			$report_str
		);

		$this->assertStringContainsString(
			'Latest version available',
			$report_str
		);

		$this->assertStringContainsString(
			'2.0.0',
			$report_str
		);

		$this->assertStringContainsString(
			'Latest version download URI',
			$report_str
		);

		$this->assertStringContainsString(
			'https://downloads.wordpress.org/themes/my-theme-2.0.0.zip',
			$report_str
		);

		$this->assertStringContainsString(
			'Title',
			$report_str
		);

		$this->assertStringContainsString(
			'Security problem in My Theme',
			$report_str
		);

		$this->assertStringContainsString(
			'Details',
			$report_str
		);

		$this->assertStringContainsString(
			VIPGOCI_WPSCAN_BASE_URL . '/vulnerability/0100100',
			$report_str
		);

		$this->assertStringContainsString(
			'Severity',
			$report_str
		);

		$this->assertStringContainsString(
			'5.0/10 (MEDIUM)',
			$report_str
		);
	}

	/**
	 * Test invalid usage, invalid 'security' field.
	 *
	 * @covers ::vipgoci_wpscan_report_comment_format_result
	 *
	 * @return void
	 */
	public function testReportResultSecurityTypeInvalid(): void {
		vipgoci_unittests_indicate_test_id( 'WpscanReportCommentFormatResultTest' );

		ob_start();

		vipgoci_wpscan_report_comment_format_result(
			'repo_owner',
			'repo_name',
			'commit12345id',
			array(
				'security' => 'invalid', // Invalid.
				'message'  => 'My Theme',
				'details'  => array(
					'slug'                => 'my-plugin',
					'url'                 => 'https://wordpress.org/themes/my-theme',
					'installed_location'  => 'themes/my-theme',
					'version_detected'    => '1.0.0',
					'latest_version'      => '2.0.0',
					'latest_download_uri' => 'https://downloads.wordpress.org/themes/my-theme-2.0.0.zip',
					'vulnerabilities'     => array(
						array(
							'id'    => '0100100',
							'title' => 'Security problem in My Theme',
						),
					),
				),
			),
			VIPGOCI_ADDON_THEME,
			array(
				50,
				60,
			)
		);

		vipgoci_unittests_remove_indication_for_test_id( 'WpscanReportCommentFormatResultTest' );

		// String should have been printed.
		$printed_data = ob_get_contents();

		ob_end_clean();

		if ( true === vipgoci_unittests_debug_mode_on() ) {
			echo $printed_data;
		}

		// Check if expected string was printed.
		$this->assertStringContainsString(
			'Internal error: Invalid $issue[security] in ',
			$printed_data,
		);
	}

	/**
	 * Test invalid usage, invalid 'issue_type' field.
	 *
	 * @covers ::vipgoci_wpscan_report_comment_format_result
	 *
	 * @return void
	 */
	public function testReportResultIssueTypeInvalid(): void {
		vipgoci_unittests_indicate_test_id( 'WpscanReportCommentFormatResultTest' );

		ob_start();

		vipgoci_wpscan_report_comment_format_result(
			'repo_owner',
			'repo_name',
			'commit12345id',
			array(
				'security' => VIPGOCI_WPSCAN_OBSOLETE,
				'message'  => 'My Theme',
				'details'  => array(
					'slug'                => 'my-plugin',
					'url'                 => 'https://wordpress.org/themes/my-theme',
					'installed_location'  => 'themes/my-theme',
					'version_detected'    => '1.0.0',
					'latest_version'      => '2.0.0',
					'latest_download_uri' => 'https://downloads.wordpress.org/themes/my-theme-2.0.0.zip',
					'vulnerabilities'     => array(
						array(
							'id'    => '0100100',
							'title' => 'Security problem in My Theme',
						),
					),
				),
			),
			'invalid', // Invalid.
			array(
				70,
				80,
			)
		);

		vipgoci_unittests_remove_indication_for_test_id( 'WpscanReportCommentFormatResultTest' );

		// String should have been printed.
		$printed_data = ob_get_contents();

		ob_end_clean();

		if ( true === vipgoci_unittests_debug_mode_on() ) {
			echo $printed_data;
		}

		// Check if expected string was printed.
		$this->assertStringContainsString(
			'Internal error: Invalid $issue_type in ',
			$printed_data,
		);
	}
}
