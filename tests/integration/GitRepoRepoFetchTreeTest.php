<?php
/**
 * Test function vipgoci_gitrepo_fetch_tree().
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
final class GitRepoRepoFetchTreeTest extends TestCase {
	/**
	 * Options array.
	 *
	 * @var $options
	 */
	private array $options = array();

	var $options_git = array(
		'git-path'			=> null,
		'github-repo-url'		=> null,
		'repo-owner'			=> null,
		'repo-name'			=> null,
	);

	var $options_git_repo_tests = array(
		'commit-test-repo-fetch-tree-1'	=> null,
		'commit-test-repo-fetch-tree-2'	=> null,
	);

	protected function setUp(): void {
		require_once __DIR__ . '/IncludesForTests.php';

		vipgoci_unittests_get_config_values(
			'git',
			$this->options_git
		);

		vipgoci_unittests_get_config_values(
			'git-repo-tests',
			$this->options_git_repo_tests
		);

		$this->options = array_merge(
			$this->options_git,
			$this->options_git_repo_tests
		);

		$this->options[ 'github-token' ] =
			vipgoci_unittests_get_config_value(
				'git-secrets',
				'github-token',
				true // Fetch from secrets file
			);
	}

	protected function tearDown(): void {
		if ( false !== $this->options['local-git-repo'] ) {
			vipgoci_unittests_remove_git_repo(
				$this->options['local-git-repo']
			);
		}
	}

	/**
	 * @covers ::vipgoci_gitrepo_fetch_tree
	 */
	public function testRepoFetchTree1() {
		$options_test = vipgoci_unittests_options_test(
			$this->options,
			array( 'github-token', 'token' ),
			$this
		);

		if ( -1 === $options_test ) {
			return;
		}

		$this->options['commit'] =
			$this->options['commit-test-repo-fetch-tree-1'];

		vipgoci_unittests_output_suppress();

		$this->options['local-git-repo'] =
			vipgoci_unittests_setup_git_repo(
				$this->options
			);


		if ( false === $this->options['local-git-repo'] ) {
			$this->markTestSkipped(
				'Could not set up git repository: ' .
					vipgoci_unittests_output_get()
			);

			return;
		}

		$this->options['token'] =
			$this->options['github-token'];

		$ret1 = vipgoci_gitrepo_fetch_tree(
			$this->options,
			$this->options['commit-test-repo-fetch-tree-1'],
			null
		);

		vipgoci_unittests_output_unsuppress();

		$this->assertSame(
			array(
				'README.md',
				'file-1.txt',
			),
			$ret1
		);
	}

	/**
	 * @covers ::vipgoci_gitrepo_fetch_tree
	 */
	public function testRepoFetchTree2() {
		$options_test = vipgoci_unittests_options_test(
			$this->options,
			array( 'github-token', 'token' ),
			$this
		);

		if ( -1 === $options_test ) {
			return;
		}

		$this->options['commit'] =
			$this->options['commit-test-repo-fetch-tree-2'];

		vipgoci_unittests_output_suppress();

		$this->options['local-git-repo'] =
			vipgoci_unittests_setup_git_repo(
				$this->options
			);


		if ( false === $this->options['local-git-repo'] ) {
			$this->markTestSkipped(
				'Could not set up git repository: ' .
					vipgoci_unittests_output_get()
			);
		}

		$this->options['token'] =
			$this->options['github-token'];

		$ret2 = vipgoci_gitrepo_fetch_tree(
			$this->options,
			$this->options['commit-test-repo-fetch-tree-2'],
			array(
				'file_extensions' => array( 'txt' )
			)
		);

		vipgoci_unittests_output_unsuppress();

		$this->assertSame(
			array(
				'file-1.txt',
			),
			$ret2
		);
	}
}
