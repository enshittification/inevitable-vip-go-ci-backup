<?php
/**
 * Test function vipgoci_github_org_teams_get().
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
 * @preserveGlobalState
 */
final class GitHubOrgTeamsTest extends TestCase {
	/**
	 * Options array
	 *
	 * @var $options
	 */
	private array $options = array(
		'github-token' => null,
		'org-name'     => null,
		'team-slug'    => null,
	);

	/**
	 * Setup function.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		require_once __DIR__ . '/IncludesForTests.php';

		foreach ( $this->options as $option_key => $option_value ) {
			$this->options[ $option_key ] =
				vipgoci_unittests_get_config_value(
					'git-secrets',
					$option_key,
					true
				);
		}
	}

	/**
	 * Teardown function.
	 *
	 * @return void
	 */
	protected function tearDown(): void {
		unset( $this->options );
	}

	/**
	 * Test common usage of the function.
	 *
	 * @covers ::vipgoci_github_org_teams_get
	 *
	 * @return void
	 */
	public function testGitHubOrgTeamsNoFiltersNoKeys() :void {
		$options_test = vipgoci_unittests_options_test(
			$this->options,
			array(),
			$this
		);

		if ( -1 === $options_test ) {
			return;
		}

		if ( empty( $this->options ) ) {
			$this->markTestSkipped(
				'Must set up ' . __FUNCTION__ . '() test'
			);

			return;
		}

		/*
		 * Test vipgoci_github_org_teams_get() without any
		 * filters and without any output sorting.
		 */

		vipgoci_unittests_output_suppress();

		$teams_res_actual = vipgoci_github_org_teams_get(
			$this->options['github-token'],
			$this->options['org-name'],
			null,
			null
		);

		vipgoci_unittests_output_unsuppress();

		$this->assertNotEmpty(
			$teams_res_actual,
			'Got no teams from vipgoci_github_org_teams_get()'
		);

		$this->assertTrue(
			isset(
				$teams_res_actual[0]->name
			)
		);

		$this->assertTrue(
			strlen(
				$teams_res_actual[0]->name
			) > 0
		);

		/*
		 * Test the caching-functionality
		 */

		vipgoci_unittests_output_suppress();

		$teams_res_actual_cached = vipgoci_github_org_teams_get(
			$this->options['github-token'],
			$this->options['org-name'],
			null,
			null
		);

		vipgoci_unittests_output_unsuppress();

		$this->assertSame(
			$teams_res_actual,
			$teams_res_actual_cached
		);

		unset( $teams_res_actual );
		unset( $teams_res_actual_cached );
	}

	/**
	 * Test common usage of the function.
	 *
	 * @covers ::vipgoci_github_org_teams_get
	 *
	 * @return void
	 */
	public function testGitHubOrgTeamsWithFilters() :void {
		$options_test = vipgoci_unittests_options_test(
			$this->options,
			array(),
			$this
		);

		if ( -1 === $options_test ) {
			return;
		}

		if ( empty( $this->options ) ) {
			$this->markTestSkipped(
				'Must set up ' . __FUNCTION__ . '() test'
			);

			return;
		}

		/*
		 * Test vipgoci_github_org_teams_get() with filters but
		 * without any output sorting.
		 */

		vipgoci_unittests_output_suppress();

		$teams_res_actual = vipgoci_github_org_teams_get(
			$this->options['github-token'],
			$this->options['org-name'],
			array(
				'slug' => $this->options['team-slug'],
			),
			null
		);

		vipgoci_unittests_output_unsuppress();

		$this->assertNotEmpty(
			$teams_res_actual,
			'Got no teams from vipgoci_github_org_teams_get()'
		);

		$this->assertTrue(
			isset(
				$teams_res_actual[0]->name
			)
		);

		$this->assertTrue(
			strlen(
				$teams_res_actual[0]->name
			) > 0
		);

		/*
		 * Test again, now the cached version.
		 */

		vipgoci_unittests_output_suppress();

		$teams_res_actual_cached = vipgoci_github_org_teams_get(
			$this->options['github-token'],
			$this->options['org-name'],
			array(
				'slug' => $this->options['team-slug'],
			),
			null
		);

		vipgoci_unittests_output_unsuppress();

		$this->assertSame(
			$teams_res_actual,
			$teams_res_actual_cached
		);

		unset( $teams_res_actual );
		unset( $teams_res_actual_cached );
	}

	/**
	 * Test common usage of the function.
	 *
	 * @covers ::vipgoci_github_org_teams_get
	 *
	 * @return void
	 */
	public function testGitHubOrgTeamsWithKeyes() :void {
		$options_test = vipgoci_unittests_options_test(
			$this->options,
			array(),
			$this
		);

		if ( -1 === $options_test ) {
			return;
		}

		if ( empty( $this->options ) ) {
			$this->markTestSkipped(
				'Must set up ' . __FUNCTION__ . '() test'
			);

			return;
		}

		/*
		 * Test vipgoci_github_org_teams_get() without filters but
		 * with output keyed.
		 */
		vipgoci_unittests_output_suppress();

		$teams_res_actual = vipgoci_github_org_teams_get(
			$this->options['github-token'],
			$this->options['org-name'],
			null,
			'slug'
		);

		vipgoci_unittests_output_unsuppress();

		$this->assertNotEmpty(
			$teams_res_actual,
			'Got no teams from vipgoci_github_org_teams_get()'
		);

		$teams_res_actual_keys = array_keys(
			$teams_res_actual
		);

		$this->assertTrue(
			isset(
				$teams_res_actual[ $teams_res_actual_keys[0] ][0]->name
			)
		);

		$this->assertTrue(
			strlen(
				$teams_res_actual[ $teams_res_actual_keys[0] ][0]->name
			) > 0
		);

		$this->assertSame(
			$teams_res_actual_keys[0],
			$teams_res_actual[ $teams_res_actual_keys[0] ][0]->slug
		);

		/*
		 * Test again, now the cached version.
		 */
		vipgoci_unittests_output_suppress();

		$teams_res_actual_cached = vipgoci_github_org_teams_get(
			$this->options['github-token'],
			$this->options['org-name'],
			null,
			'slug'
		);

		vipgoci_unittests_output_unsuppress();

		$this->assertSame(
			$teams_res_actual,
			$teams_res_actual_cached
		);

		unset( $teams_res_actual );
		unset( $teams_res_actual_cached );
	}
}
