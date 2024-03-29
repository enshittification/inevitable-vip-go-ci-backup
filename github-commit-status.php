#!/usr/bin/env php
<?php
/**
 * Utility to submit GitHub commit status to the
 * GitHub API.
 *
 * @package Automattic/vip-go-ci
 */

declare(strict_types=1);

define( 'VIPGOCI_INCLUDED', true );

require_once __DIR__ . '/requires.php';

/*
 * Configure PHP error reporting.
 */
vipgoci_set_php_error_reporting();

/*
 * Recognized options, get options
 */
$options_recognized = array(
	'env-options:',
	'repo-owner:',
	'repo-name:',
	'github-token:',
	'github-commit:',
	'build-state:',
	'build-description:',
	'build-context:',
);

$options = getopt(
	'',
	$options_recognized
);

/*
 * Options to remove of any sensitive
 * detail when cleaned later.
 */
vipgoci_options_sensitive_clean(
	null,
	array(
		'github-token',
	)
);


/*
 * Parse options
 */
vipgoci_option_array_handle(
	$options,
	'env-options',
	array(),
	null,
	',',
	false
);

vipgoci_options_read_env(
	$options,
	$options_recognized
);

/*
 * Verify we have all the options
 * we need.
 */
if (
	( ! isset(
		$options['repo-owner'],
		$options['repo-name'],
		$options['github-token'],
		$options['github-commit'],
		$options['build-state'],
		$options['build-description'],
		$options['build-context']
	) )
	||
	(
		isset( $options['help'] )
	)
) {
	print 'Usage: ' . $argv[0] . PHP_EOL .
		PHP_EOL .
		"\t" . '--repo-owner=STRING            Specify repository owner, can be an organization' . PHP_EOL .
		"\t" . '--repo-name=STRING             Specify name of the repository' . PHP_EOL .
		"\t" . '--github-token=STRING          The access-token to use to communicate with GitHub' . PHP_EOL .
		"\t" . '--github-commit=STRING         Specify the exact commit to set state for' . PHP_EOL .
		PHP_EOL .
		"\t" . '--build-state=STRING           Specify build state, one of: "pending", "failure", ' . PHP_EOL .
		"\t" . '                               and "success" are valid.' . PHP_EOL .
		"\t" . '--build-description=STRING     Specify description for end-user, displayed along with ' . PHP_EOL .
		"\t" . '                               state.' . PHP_EOL .
		"\t" . '--build-context=STRING         Specify context, a consistent identifier used across ' . PHP_EOL .
		"\t" . '                               all the build states' . PHP_EOL .
		PHP_EOL .
		"\t" . '--help                         Prints this message.' . PHP_EOL .
		PHP_EOL .
		'All options are mandatory.' . PHP_EOL;

	exit( VIPGOCI_EXIT_USAGE_ERROR );
}

/*
 * Verify that --build-state is of valid
 * value.
 */
switch ( $options['build-state'] ) {
	case 'pending':
	case 'failure':
	case 'success':
		break;

	default:
		vipgoci_sysexit(
			'Invalid parameter for --build-state, only "pending", "failure", and "success" are valid',
			array(
				'build-state' => $options['build-state'],
			),
			VIPGOCI_EXIT_USAGE_ERROR
		);
}

/*
 * Log that we are setting build
 * status and set it.
 */
vipgoci_log(
	'Setting build status for commit ...',
	array(
		'options' => vipgoci_options_sensitive_clean(
			$options
		),
	)
);

vipgoci_github_status_create(
	$options['repo-owner'],
	$options['repo-name'],
	$options['github-token'],
	$options['github-commit'],
	$options['build-state'],
	'',
	$options['build-description'],
	$options['build-context']
);

vipgoci_log(
	'Finished, exiting',
);


