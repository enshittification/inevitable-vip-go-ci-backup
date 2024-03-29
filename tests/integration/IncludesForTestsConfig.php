<?php

declare( strict_types=1 );

/**
 * Get configuration value from an INI config file.
 *
 * @param string $section     Section of the configuration file selected.
 * @param string $key         Fetch value for this key.
 * @param bool   $secret_file Use secret file rather than the public one.
 *
 * @return null|string
 */
function vipgoci_unittests_get_config_value(
	string $section,
	string $key,
	bool $secret_file = false
) :?string {
	if ( false === $secret_file ) {
		$ini_array = parse_ini_file(
			VIPGOCI_UNIT_TESTS_INI_DIR_PATH . '/config.ini',
			true
		);
	} else {
		$ini_array = parse_ini_file(
			VIPGOCI_UNIT_TESTS_INI_DIR_PATH . '/config-secrets.ini',
			true
		);
	}

	if ( false === $ini_array ) {
		return null;
	}

	if ( isset(
		$ini_array
			[ $section ]
			[ $key ]
	) ) {
		if ( empty(
			$ini_array
				[ $section ]
				[ $key ]
		) ) {
			return null;
		}

		return $ini_array
			[ $section ]
			[ $key ];
	}

	return null;
}

/**
 * Get configuration values from an INI config file,
 *
 * @param string $section     Section of the configuration file selected.
 * @param array  $config_arr  Fetch values for these keys.
 * @param bool   $secret_file Use secret file rather than the public one.
 *
 * @return void
 */
function vipgoci_unittests_get_config_values(
	string $section,
	array &$config_arr,
	bool $secret_file = false
) :void {
	foreach (
		array_keys( $config_arr ) as $config_key
	) {
		$config_arr[ $config_key ] =
			vipgoci_unittests_get_config_value(
				$section,
				$config_key,
				$secret_file
			);

		if ( empty( $config_arr[ $config_key ] ) ) {
			$config_arr[ $config_key ] = null;
		}
	}
}

/**
 * Test if all options required for test are in place.
 *
 * @param array  $options              Array of options.
 * @param array  $options_not_required Array of options not required.
 * @param object $test_instance        Instance of test class.
 *
 * @return int
 */
function vipgoci_unittests_options_test(
	array $options,
	array $options_not_required,
	object &$test_instance
) :int {
	$missing_options_str = '';

	$options_keys = array_keys(
		$options
	);

	foreach (
		$options_keys as $option_key
	) {
		if ( in_array(
			$option_key,
			$options_not_required,
			true
		) ) {
			continue;
		}

		if (
			( '' === $options[ $option_key ] ) ||
			( null === $options[ $option_key ] )
		) {
			if ( '' !== $missing_options_str ) {
				$missing_options_str .= ', ';
			}

			$missing_options_str .= $option_key;
		}
	}

	if ( '' !== $missing_options_str ) {
		$test_instance->markTestSkipped(
			'Skipping test, not configured correctly, as some options are missing (' . $missing_options_str . ')'
		);

		return -1;
	}

	return 0;
}

/**
 * Returns true if to skip tests that write data via GitHub API,
 * otherwise false.
 *
 * @return bool True when to skip tests that write data, otherwise false.
 */
function vipgoci_unittests_get_skip_github_write_tests() :bool {
	$config_arr = array(
		'github-skip-write-tests' => null,
	);

	vipgoci_unittests_get_config_values(
		'git-secrets',
		$config_arr,
		true
	);

	if (
		( isset( $config_arr['github-skip-write-tests'] ) ) &&
		( '1' === $config_arr['github-skip-write-tests'] )
	) {
		return true;
	}

	return false;
}
/**
 * Returns true if to skip tests that write data via GitHub API
 * and will mark test as skipped as well. Otherwise returns false.
 *
 * @param object|null $test_instance Instance of test class.
 *
 * @return bool True when to skip tests that write data, otherwise false.
 */
function vipgoci_unittests_skip_github_write_tests(
	object|null &$test_instance
) :bool {
	if ( true === vipgoci_unittests_get_skip_github_write_tests() ) {
		if ( null !== $test_instance ) {
			$test_instance->markTestSkipped(
				'Skipping test, should not be run as configured to skip tests that write data via GitHub API and this test does so'
			);
		}

		return true;
	}

	return false;
}
