<?php
/**
 * Test function vipgoci_github_pr_review_events_get().
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
final class GitHubPrReviewEventsGetTest extends TestCase {
	/**
	 * Options array.
	 *
	 * @var $options
	 */
	private array $options = array();

	var $options_git = array(
		'repo-owner'                    => null,
		'repo-name'                     => null,
	);

	var $options_git_repo_tests = array(
		'test-github-pr-reviews-event-get-pr-number'	=> null,
		'test-github-pr-reviews-event-get-username'	=> null,
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

		$this->options['github-token'] =
			vipgoci_unittests_get_config_value(
				'git-secrets',
				'github-token',
				true // Fetch from secrets file.
			);

		if ( empty( $this->options['github-token'] ) ) {
			$this->options['github-token'] = '';
		}

		$this->options['token'] = $this->options['github-token'];
	}

	protected function tearDown(): void {
		unset( $this->options_git );
		unset( $this->options_git_repo_tests );
		unset( $this->options );
	}

	/**
	 * @covers ::vipgoci_github_pr_review_events_get
	 */
	public function testGitHubPrReviewEventsGet_no_filters() {
		vipgoci_unittests_output_suppress();

		$issue_events = vipgoci_github_pr_review_events_get(
			$this->options,
			(int) $this->options['test-github-pr-reviews-event-get-pr-number'],
			null,
			false
		);

		vipgoci_unittests_output_unsuppress();

		$this->assertNotEmpty(
			$issue_events,
			'Got empty response from vipgoci_github_pr_review_events_get()!'
		);

		$this->assertTrue(
			isset(
				$issue_events[0]->id
			)
		);

		$this->assertTrue(
			is_numeric(
				$issue_events[0]->id
			)
		);

		$this->assertTrue(
			isset(
				$issue_events[0]->event
			)
		);

		$this->assertTrue(
			strlen(
				$issue_events[0]->event
			) > 0
		);


		/*
		 * Perform testing again, now to test caching.
		 */

		vipgoci_unittests_output_suppress();

		$issue_events_cached = vipgoci_github_pr_review_events_get(
			$this->options,
			(int) $this->options['test-github-pr-reviews-event-get-pr-number'],
			null,
			false
		);

		vipgoci_unittests_output_unsuppress();

		$this->assertSame(
			$issue_events,
			$issue_events_cached
		);
	}

	/**
	 * @covers ::vipgoci_github_pr_review_events_get
	 */
	public function testGitHubPrReviewEventsGet_with_filters() {
		vipgoci_unittests_output_suppress();

		$issue_events = vipgoci_github_pr_review_events_get(
			$this->options,
			(int) $this->options['test-github-pr-reviews-event-get-pr-number'],
			array(
				'event_type'	=> 'labeled',
				'actors_logins'	=> array(
					$this->options['test-github-pr-reviews-event-get-username']
				),
			),
			false
		);

		vipgoci_unittests_output_unsuppress();

		$this->assertNotEmpty(
			$issue_events,
			'Got empty response from vipgoci_github_pr_review_events_get()!'
		);

		$this->assertTrue(
			isset(
				$issue_events[0]->id
			)
		);

		$this->assertTrue(
			is_numeric(
				$issue_events[0]->id
			)
		);

		$this->assertTrue(
			isset(
				$issue_events[0]->event
			)
		);

		$this->assertTrue(
			strlen(
				$issue_events[0]->event
			) > 0
		);

		foreach ( $issue_events as $issue_event ) {
			$this->assertSame(
				$this->options['test-github-pr-reviews-event-get-username'],
				$issue_event->actor->login
			);

			$this->assertSame(
				'labeled',
				$issue_event->event
			);
		}


		/*
		 * Do the testing again, now using cached data
		 */

		vipgoci_unittests_output_suppress();
	
		$issue_events_cached = vipgoci_github_pr_review_events_get(
			$this->options,
			(int) $this->options['test-github-pr-reviews-event-get-pr-number'],
			array(
				'event_type'	=> 'labeled',
				'actors_logins'	=> array(
					$this->options['test-github-pr-reviews-event-get-username']
				),
			),
			false
		);

		vipgoci_unittests_output_unsuppress();

		$this->assertSame(
			$issue_events,
			$issue_events_cached
		);
	}

	/**
	 * @covers ::vipgoci_github_pr_review_events_get
	 */
	public function testGitHubPrReviewEventsGet_with_review_ids_only() {
		vipgoci_unittests_output_suppress();

		$issue_events = vipgoci_github_pr_review_events_get(
			$this->options,
			(int) $this->options['test-github-pr-reviews-event-get-pr-number'],
			null,
			true
		);

		vipgoci_unittests_output_unsuppress();


		$this->assertNotEmpty(
			$issue_events,
			'Got empty response from vipgoci_github_pr_review_events_get()!'
		);

		foreach( $issue_events as $issue_event ) {
			$this->assertTrue(
				is_numeric(
					$issue_event
				)
			);
		}

		/*
		 * Now with caching.
		 */

		vipgoci_unittests_output_suppress();

		$issue_events_cached = vipgoci_github_pr_review_events_get(
			$this->options,
			(int) $this->options['test-github-pr-reviews-event-get-pr-number'],
			null,
			true
		);

		vipgoci_unittests_output_unsuppress();
	
		$this->assertSame(
			$issue_events,
			$issue_events_cached
		);
	}
}
