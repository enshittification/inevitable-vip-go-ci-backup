<?php
/**
 * Logic to secure output generated by vip-go-ci.
 *
 * @package Automattic/vip-go-ci
 */

declare(strict_types=1);

/**
 * Sanitize string to ensure it contains
 * only characters found in version numbers.
 *
 * @param string $version_number Version number to sanitize.
 *
 * @return string Sanitized version number.
 */
function vipgoci_output_sanitize_version_number(
	string $version_number
) :string {
	return preg_replace(
		'/[^a-zA-Z0-9\.]/',
		'',
		$version_number
	);
}

/**
 * Encode string so it does not contain
 * HTML tags and is escaped properly.
 *
 * @param string $text_string String to escape.
 *
 * @return string HTML escaped text string.
 */
function vipgoci_output_html_escape(
	string $text_string
) :string {
	$text_string = strip_tags( $text_string );

	return filter_var(
		$text_string,
		FILTER_SANITIZE_FULL_SPECIAL_CHARS
	);
}

