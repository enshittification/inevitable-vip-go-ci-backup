<?php
/**
 * WordPress Core functionality needed for vip-go-ci.
 *
 * Some of these functions were borrowed and adapted from WordPress and/or b2.
 *
 * WordPress: Copyright 2011-2022 by the contributors of WordPress.
 * b2: Copyright 2001, 2002 Michel Valdrighi - https://cafelog.com
 *
 * @package Automattic/vip-go-ci
 */

declare(strict_types=1);

/**
 * Retrieve metadata from a file.
 *
 * Searches for metadata in the first 8 KB of a file, such as a plugin or theme.
 * Each piece of metadata must be on its own line. Fields can not span multiple
 * lines, the value will get cut at the end of the first line.
 *
 * If the file data is not within that first 8 KB, then the author should correct
 * their plugin file and move the data headers to the top.
 *
 * Adopted from WordPress: https://core.trac.wordpress.org/browser/tags/6.0/src/wp-includes/functions.php#L6611
 *
 * @link https://codex.wordpress.org/File_Header
 *
 * @param string $file        Absolute path to the file to retrieve metadata from.
 * @param array  $all_headers List of headers, in the format `array( 'HeaderKey' => 'Header Name' )`.
 *
 * @return array Array of file header values keyed by header name. For example:
 * Array(
 *   [HeaderKey1] => My value
 *   [HeaderKey2] => My value 2
 * )
 */
function vipgoci_wpcore_misc_get_file_wp_headers(
	string $file,
	array $all_headers
) :array {
	// Pull only the first 8 KB of the file in.
	$file_data = file_get_contents(
		$file,
		false,
		null,
		0,
		8 * VIPGOCI_KB_IN_BYTES
	);

	if ( false === $file_data ) {
		$file_data = '';
	}

	// Make sure we catch CR-only line endings.
	$file_data = str_replace( "\r", "\n", $file_data );

	foreach ( $all_headers as $field => $regex ) {
		if (
			( preg_match(
				'/^(?:[ \t]*<\?php)?[ \t\/*#@]*' . preg_quote( $regex, '/' ) . ':(.*)$/mi',
				$file_data,
				$match
			) )
			&&
			( $match[1] )
		) {
			$all_headers[ $field ] = vipgoci_wpcore_misc_cleanup_header_comment(
				$match[1]
			);
		} else {
			$all_headers[ $field ] = '';
		}
	}

	/*
	 * WordPress runs headers through a translation, we skip
	 * this as we do not have access to the translations.
	 */
	if ( isset( $all_headers['Name'] ) ) {
		$all_headers['Title'] = $all_headers['Name'];
	}

	if ( isset( $all_headers['Author'] ) ) {
		$all_headers['AuthorName'] = $all_headers['Author'];
	}

	return $all_headers;
}

/**
 * Strip close comment and close php tags from file headers used by WordPress.
 *
 * Adopted from https://core.trac.wordpress.org/browser/tags/6.0/src/wp-includes/functions.php#L6544
 *
 * @param string $str Header comment to clean up.
 *
 * @return string String with close comment/php tags removed.
 */
function vipgoci_wpcore_misc_cleanup_header_comment(
	string $str
) :string {
	return trim( preg_replace( '/\s*(?:\*\/|\?>).*/', '', $str ) );
}

/**
 * Attempts to fetch WordPress theme/plugin headers, such as
 * Name, AuthorName, Version, and so forth from a file.
 * Also attempts to determine if file is part of a theme or a plugin.
 *
 * @param string $file_name              Path to file to try to fetch headers from.
 * @param array  $plugin_file_extensions File extensions to consider when determining plugins to analyze.
 * @param array  $theme_file_extensions  File extensions to consider when determining themes to analyze.
 *
 * @return null|array Null on failure to get headers.
 * On success, associative array with type, version, and
 * plugin/theme headers.
 */
function vipgoci_wpcore_misc_get_addon_headers_and_type(
	string $file_name,
	array $plugin_file_extensions,
	array $theme_file_extensions
) :null|array {
	vipgoci_log(
		'Attempting to determine plugin/theme headers for file',
		array(
			'file_name' => $file_name,
		),
		2
	);

	// By default, assume no headers where found.
	$type = null;

	$file_extension = pathinfo(
		$file_name,
		PATHINFO_EXTENSION
	);

	if ( true === in_array(
		$file_extension,
		$plugin_file_extensions,
		true
	) ) {
		/*
		 * Try to retrieve plugin headers.
		 */
		$plugin_data = vipgoci_wpcore_misc_get_file_wp_headers(
			$file_name,
			array(
				'Name'        => 'Plugin Name',
				'PluginURI'   => 'Plugin URI',
				'Version'     => 'Version',
				'Description' => 'Description',
				'Author'      => 'Author',
				'AuthorURI'   => 'Author URI',
				'TextDomain'  => 'Text Domain',
				'DomainPath'  => 'Domain Path',
				'Network'     => 'Network',
				'RequiresWP'  => 'Requires at least',
				'RequiresPHP' => 'Requires PHP',
				'UpdateURI'   => 'Update URI',
			),
		);

		if (
			( ! empty( $plugin_data['Name'] ) ) &&
			( ! empty( $plugin_data['Author'] ) ) &&
			( ! empty( $plugin_data['Version'] ) )
		) {
			$type             = VIPGOCI_ADDON_PLUGIN;
			$addon_headers    = $plugin_data;
			$version_detected = $plugin_data['Version'];
		}
	} elseif ( true === in_array(
		$file_extension,
		$theme_file_extensions,
		true
	) ) {
		/*
		 * If theme file extension, try fetching headers.
		 */
		$theme_data = vipgoci_wpcore_misc_get_file_wp_headers(
			$file_name,
			array(
				'Name'        => 'Theme Name',
				'ThemeURI'    => 'Theme URI',
				'Description' => 'Description',
				'Author'      => 'Author',
				'AuthorURI'   => 'Author URI',
				'Version'     => 'Version',
				'Template'    => 'Template',
				'Status'      => 'Status',
				'TextDomain'  => 'Text Domain',
				'DomainPath'  => 'Domain Path',
				'RequiresWP'  => 'Requires at least',
				'RequiresPHP' => 'Requires PHP',
				'UpdateURI'   => 'Update URI',
			),
		);

		if (
			( ! empty( $theme_data['Name'] ) ) &&
			( ! empty( $theme_data['Author'] ) ) &&
			( ! empty( $theme_data['Version'] ) )
		) {
			$type             = VIPGOCI_ADDON_THEME;
			$addon_headers    = $theme_data;
			$version_detected = $theme_data['Version'];
		}
	}

	if ( null === $type ) {
		vipgoci_log(
			'Unable to determine plugin/theme headers for file',
			array(
				'file_name' => $file_name,
			),
			2
		);

		return null;
	} else {
		vipgoci_log(
			'Determined plugin/theme headers for file',
			array(
				'file_name'     => $file_name,
				'type'          => $type,
				'addon_headers' => $addon_headers,
			),
			2
		);

		// Some headers were retrieved, return what we collected.
		return array(
			'type'             => $type,
			'addon_headers'    => $addon_headers,
			'name'             => $addon_headers['Name'],
			'version_detected' => $version_detected,
			'file_name'        => $file_name,
		);
	}
}

/**
 * Determine "local" slug used when querying the WordPress.org API.
 *
 * @param string $addon_type    Addon type, plugin or theme.
 * @param string $full_path     Full path to plugin or theme in git repository.
 * @param string $relative_path Relative path to plugin or theme.
 *
 * @return string "local" slug.
 */
function vipgoci_wpcore_misc_determine_local_slug(
	string $addon_type,
	string $full_path,
	string $relative_path
) :string {
	if ( VIPGOCI_ADDON_THEME === $addon_type ) {
		// Special case for themes.
		$key = basename( dirname( $full_path ) );
	} elseif (
		( VIPGOCI_ADDON_PLUGIN === $addon_type ) &&
		( true === str_contains( $relative_path, '/' ) )
	) {
		$relative_path_dirname = dirname( $relative_path );

		$relative_path_dirname_arr = array_reverse(
			explode( '/', $relative_path_dirname )
		);

		$relative_path_dirname_dircount = count(
			$relative_path_dirname_arr
		);

		/*
		 * Only return "local" slug including directory if there are two or more
		 * directories in path, but not when last directory name includes certain
		 * names that will result in bogus results.
		 */
		if (
			( $relative_path_dirname_dircount >= 2 ) &&
			( false === in_array(
				strtolower( $relative_path_dirname_arr[0] ),
				array(
					'plugins',
					'themes',
					'library',
				),
				true
			) )
		) {
			$key = $relative_path_dirname_arr[0] . '/' .
				basename( $relative_path );
		} else {
			$key = basename( $relative_path );
		}
	} else {
		$key = basename( $relative_path );
	}

	return $addon_type . '-' . $key;
}

/**
 * Get list of plugins or themes found in $path, return as array of
 * key-value pairs.
 *
 * This functionality aims for compatibility with get_plugins() in WordPress.
 * The function is adopted from WordPress: https://core.trac.wordpress.org/browser/tags/6.0/src/wp-admin/includes/plugin.php#L254
 *
 * @param string $local_git_repo         Local git repository.
 * @param string $relative_path          Relative path to scan for plugins and themes. Usually this would point a structure similar to wp-content/plugins.
 * @param array  $plugin_file_extensions File extensions to consider when determining plugins to analyze.
 * @param array  $theme_file_extensions  File extensions to consider when determining themes to analyze.
 * @param bool   $process_subdirectories If to process sub-directories.
 *
 * @link https://developer.wordpress.org/reference/functions/get_plugins/
 *
 * @return array List of plugins, with array slug as key, value array with details. Example:
 * Array(
 *   [hello/hello.php] => Array(
 *     [type] => vipgoci-addon-plugin
 *     [addon_headers] => Array(
 *       [Name] => Hello Dolly
 *       [PluginURI] => http://wordpress.org/plugins/hello-dolly/
 *       [Version] => 1.6
 *       [Description] => This is not just a plugin ...
 *       [Author] => Matt Mullenweg
 *       [AuthorURI] => http://ma.tt/
 *       [Title] => Hello Dolly
 *       [AuthorName] => Matt Mullenweg
 *     )
 *     [name] => Hello Dolly
 *     [version_detected] => 1.6
 *     [filename] => /tmp/plugins/hello/hello.php
 *   )
 * )
 */
function vipgoci_wpcore_misc_scan_directory_for_addons(
	string $local_git_repo,
	string $relative_path,
	array $plugin_file_extensions,
	array $theme_file_extensions,
	bool $process_subdirectories = true
): array {
	$scan_path = $local_git_repo . DIRECTORY_SEPARATOR . $relative_path;

	if (
		( false === is_dir( $scan_path ) ) ||
		( false === is_readable( $scan_path ) )
	) {
		vipgoci_log(
			'Unable to scan directory for plugins/themes, skipping',
			array(
				'scan_path' => $scan_path,
			),
			2
		);

		return array();
	}

	/*
	 * Get an array of files in directories and subdirectories found in path.
	 */
	$addon_files = vipgoci_scandir_git_repo(
		$scan_path,
		$process_subdirectories,
		array(
			'file_extensions' => array_merge(
				$plugin_file_extensions,
				$theme_file_extensions
			),
		)
	);

	/*
	 * Compile list of plugins based on $addon_files
	 * and return the result.
	 */

	if ( empty( $addon_files ) ) {
		vipgoci_log(
			'No plugins/themes found while scanning directory',
			array(
				'relative_path' => $relative_path,
			),
			2
		);

		return array();
	}

	$wp_addons = array();

	foreach ( $addon_files as $addon_file ) {
		$tmp_path = $scan_path . DIRECTORY_SEPARATOR . $addon_file;

		if ( ! is_readable( $tmp_path ) ) {
			continue;
		}

		$addon_data = vipgoci_wpcore_misc_get_addon_headers_and_type(
			$tmp_path,
			$plugin_file_extensions,
			$theme_file_extensions
		);

		// When no headers are found in file, ignore file.
		if ( empty( $addon_data['addon_headers']['Name'] ) ) {
			continue;
		}

		/*
		 * Calculate 'local slug'.
		 */
		$wp_addon_key = vipgoci_wpcore_misc_determine_local_slug(
			$addon_data['type'],
			$tmp_path,
			$relative_path . DIRECTORY_SEPARATOR . $addon_file
		);

		$wp_addons[ $wp_addon_key ] = $addon_data;
	}

	vipgoci_log(
		'Scanned directory for plugins/themes',
		array(
			'local_git_repo' => $local_git_repo,
			'relative_path'  => $relative_path,
			'wp_addons'      => $wp_addons,
		),
		2
	);

	return $wp_addons;
}

/**
 * Attempts to analyze plugin or theme data to determine WordPress.org
 * slugs for them, using the WordPress.org API. Will return array with
 * slugs found along with other information from the API.
 *
 * Parts adopted from WordPress: https://core.trac.wordpress.org/browser/tags/6.0/src/wp-includes/update.php#L257
 *
 * @param array $addons_data Information about plugins/themes. For example:
 * Array(
 *   [hello/hello.php] => Array(
 *     [type] => vipgoci-addon-plugin
 *     [addon_headers] => Array(
 *       [Name] => Hello Dolly
 *       [PluginURI] => http://wordpress.org/plugins/hello-dolly/
 *       [Version] => 1.6
 *       [Description] => This is not just a plugin ...
 *       [Author] => Matt Mullenweg
 *       [AuthorURI] => http://ma.tt/
 *       [Title] => Hello Dolly
 *       [AuthorName] => Matt Mullenweg
 *     )
 *   )
 * ) // End of array.
 *
 * @return null|array Null on failure. Otherwise returns array of plugins for which WordPress.org
 *                    API gave information about. For example:
 * Array(
 *   [hello/hello.php] => Array( // API returned information.
 *     [id] => w.org/plugins/hello-dolly
 *     [slug] => hello-dolly
 *     [plugin] => hello.php
 *     [new_version] => 1.7.2
 *     [url] => https://wordpress.org/plugins/hello-dolly/
 *     [package] => https://downloads.wordpress.org/plugin/hello-dolly.1.7.2.zip
 *     ...
 *   ),
 *   [custom-plugin/custom-plugin.php] => null // API returned no information.
 */
function vipgoci_wpcore_api_determine_slug_and_other_for_addons(
	array $addons_data
) :null|array {
	vipgoci_log(
		'Preparing to query WordPress.org API about plugins/themes',
		array(
			'addons_data' => $addons_data,
		),
		0
	);

	// Data to send to WordPress.org API.
	$addon_data_to_send = array(
		VIPGOCI_ADDON_PLUGIN => array(),
		VIPGOCI_ADDON_THEME  => array(),
	);

	// Data collected about addons.
	$slugs_by_addon = array();

	if ( empty( $addons_data ) ) {
		// Got no plugins/themes to query API for, return empty array.
		vipgoci_log(
			'No plugin/themes to query WordPress.org API about, returning empty',
			array(
				'addons_data' => $addons_data,
			),
			0,
			true // Log to IRC.
		);

		return array();
	}

	foreach ( $addons_data as $key => $data_item ) {
		$key = str_replace(
			array( VIPGOCI_ADDON_PLUGIN . '-', VIPGOCI_ADDON_THEME . '-' ),
			array( '', '' ),
			$key
		);

		if ( empty( $data_item['addon_headers'] ) ) {
			vipgoci_log(
				'No addon headers found for key, unable to query WordPress.org API, skipping',
				array(
					'key' => $key,
				),
				0,
				true // Log to IRC.
			);

			continue;
		}

		if ( ! empty( $data_item['addon_headers']['UpdateURI'] ) ) {
			$update_uri_host = parse_url(
				$data_item['addon_headers']['UpdateURI'],
				PHP_URL_HOST
			);

			if (
				( 'false' === $data_item['addon_headers']['UpdateURI'] ) ||
				(
					( null !== $update_uri_host ) &&
					( false !== $update_uri_host ) &&
					( is_string( $update_uri_host ) ) &&
					( strlen( $update_uri_host ) > 0 ) &&
					( true !== vipgoci_string_found_in_substrings_array(
						VIPGOCI_WPSCAN_UPDATEURI_WP_ORG_URLS,
						$update_uri_host,
						true
					) )
				)
			) {
				vipgoci_log(
					'Skipping addon, UpdateURI header specified and does ' .
						'not match WordPress.org URIs or is set to "false"',
					array(
						'data_item'             => $data_item,
						'update_uri_host'       => $update_uri_host,
						'UpdateURI_WP_org_URIs' => VIPGOCI_WPSCAN_UPDATEURI_WP_ORG_URLS,
					),
					0,
					true // Log to IRC.
				);

				continue;
			}
		}

		$addon_data_to_send[ $data_item['type'] ][ $key ] = $data_item['addon_headers'];

		$slugs_by_addon[ $data_item['type'] . '-' . $key ] = null;
	}

	foreach ( array( VIPGOCI_ADDON_PLUGIN, VIPGOCI_ADDON_THEME ) as $addon_query_type ) {
		if ( empty( $addon_data_to_send[ $addon_query_type ] ) ) {
			continue;
		}

		$addon_query_type_short = VIPGOCI_ADDON_PLUGIN === $addon_query_type ?
			'plugins' : 'themes';

		$api_url = VIPGOCI_WORDPRESS_ORG_API_BASE_URL . '/' .
			rawurlencode( $addon_query_type_short ) . '/' .
			'update-check/1.1/';

		$api_query_data = array(
			$addon_query_type_short => json_encode(
				array(
					$addon_query_type_short =>
						$addon_data_to_send[ $addon_query_type ],
				)
			),
		);

		if ( VIPGOCI_ADDON_PLUGIN === $addon_query_type ) {
			$api_query_data['all'] = 'true';
		} elseif ( VIPGOCI_ADDON_THEME === $addon_query_type ) {
			$api_query_data['translations'] = json_encode( array() );
			$api_query_data['locale']       = json_encode( array() );
		}

		$api_data_raw = vipgoci_http_api_post_url(
			$api_url,
			$api_query_data,
			null, // No access token required.
			false, // HTTP POST.
			false, // Do not JSON encode.
			CURL_HTTP_VERSION_1_1, // Use HTTP version 1.1 due to problems with HTTP 2.
			VIPGOCI_HTTP_API_CONTENT_TYPE_X_WWW_FORM_URLENCODED // Use custom HTTP content-type header.
		);

		if ( is_int( $api_data_raw ) ) {
			vipgoci_log(
				'Unable to get information from WordPress.org API about ' . $addon_query_type_short,
				array(
					'addon_data'   => $addon_data_to_send[ $addon_query_type ],
					'api_data_raw' => $api_data_raw,
				),
				0,
				true // Log to IRC.
			);

			continue;
		}

		$api_data = json_decode(
			$api_data_raw,
			true
		);

		if ( ! is_array( $api_data ) ) {
			vipgoci_log(
				'Unable to JSON decode information from WordPress.org API about ' . $addon_query_type_short,
				array(
					'addon_data'   => $addon_data_to_send[ $addon_query_type ],
					'api_data_raw' => $api_data_raw,
					'api_data'     => $api_data,
				),
				0,
				true // Log to IRC.
			);

			continue;
		}

		/*
		 * The API will return with more than one potential
		 * result array; search both for data.
		 */
		foreach ( $api_data['no_update'] as $key => $data_item ) {
			$slugs_by_addon[ $addon_query_type . '-' . $key ] = $data_item;
		}

		foreach ( $api_data[ $addon_query_type_short ] as $key => $data_item ) {
			if ( ! isset( $slugs_by_addon[ $addon_query_type . '-' . $key ] ) ) {
				$slugs_by_addon[ $addon_query_type . '-' . $key ] = $data_item;
			}
		}

		// API provides slug in 'theme' field, handle this.
		if (
			( VIPGOCI_ADDON_THEME === $addon_query_type ) &&
			( isset( $slugs_by_addon[ $addon_query_type . '-' . $key ]['theme'] ) )
		) {
			$slugs_by_addon[ $addon_query_type . '-' . $key ]['slug'] = $slugs_by_addon[ $addon_query_type . '-' . $key ]['theme'];
		}

		/*
		 * Verify that slug is valid; if not skip result.
		 */
		if (
			( isset(
				$slugs_by_addon[ $addon_query_type . '-' . $key ]['slug']
			) ) &&
			( false === vipgoci_validate_slug(
				$slugs_by_addon[ $addon_query_type . '-' . $key ]['slug']
			) )
		) {
			vipgoci_log(
				'Invalid slug received from WordPress.org API, skipping result',
				array(
					'addon_query_type_short' => $addon_query_type_short,
					'api_data_raw'           => $api_data_raw,
					'api_data'               => $api_data,
				)
			);

			unset( $slugs_by_addon[ $addon_query_type . '-' . $key ] );

			continue;
		}
	}

	vipgoci_log(
		'Got plugin/theme information from WordPress.org API',
		array(
			'addons_data'    => $addons_data,
			'slugs_by_addon' => $slugs_by_addon,
		),
		0
	);

	return $slugs_by_addon;
}

/**
 * Loops through plugins found, assigns slugs found,
 * version numbers, etc. Ensures that if must-have
 * fields are missing, the addon is not included in
 * results.
 *
 * @param array $addons_found   List of plugins, with array slug as key, value array with details.
 * @param array $addons_details Array of plugins with info from WordPress.org API.
 *
 * @return array Addon information, with slugs etc assigned.
 */
function vipgoci_wpcore_misc_assign_addon_fields(
	array $addons_found,
	array $addons_details
) :array {
	$addon_fields_must_have = array(
		'slug',
		'new_version',
		'package',
		'url',
	);

	foreach ( $addons_found as $addon_key => $addon_item ) {
		if ( ! isset( $addons_details[ $addon_key ] ) ) {
			continue;
		}

		$addon_fields_missing = array_values(
			array_diff(
				$addon_fields_must_have,
				array_keys(
					$addons_details[ $addon_key ]
				)
			)
		);

		if ( count( $addon_fields_missing ) > 0 ) {
			vipgoci_log(
				'Skipping addon as some information was missing from API response',
				array(
					'addon_key'              => $addon_key,
					'addon_fields_must_have' => $addon_fields_must_have,
					'addon_fields_missing'   => $addon_fields_missing,
				)
			);

			unset( $addons_found[ $addon_key ] );

			continue;
		}

		/*
		 * Save must-have and optional fields in results array.
		 */
		$addon_fields = array_merge(
			$addon_fields_must_have,
			array( 'id', 'plugin' )
		);

		foreach ( $addon_fields as $_field_id ) {
			if ( isset( $addons_details[ $addon_key ][ $_field_id ] ) ) {
				$addons_found[ $addon_key ][ $_field_id ] =
					$addons_details[ $addon_key ][ $_field_id ];
			}
		}
	}

	return $addons_found;
}


/**
 * Get header data for plugins or themes in a directory ($path), attempt
 * to determine slugs and fetch other information from WordPress.org
 * API about the plugins/themes, return the information after processing.
 *
 * @param string $local_git_repo         Path to local git repository.
 * @param string $relative_path          Relative path to directory to analyze.
 * @param array  $plugin_file_extensions File extensions to consider when determining plugins to analyze.
 * @param array  $theme_file_extensions  File extensions to consider when determining themes to analyze.
 * @param bool   $process_subdirectories If to process sub-directories.
 *
 * @return array Information about plugins or themes found. Includes
 *               headers found in the plugin/theme, version number of
 *               the plugin/theme, along with information from
 *               WordPress.org API on latest version, download URL, etc.
 *               For example:
 * Array(
 *   [hello/hello.php] => Array(
 *     [type] => vipgoci-addon-plugin
 *     [addon_headers] => Array(
 *       [Name] => Hello Dolly
 *       [PluginURI] => http://wordpress.org/plugins/hello-dolly/
 *       [Version] => 1.6
 *       [Description] => This is not just a plugin, ...
 *       [Author] => Matt Mullenweg
 *       [AuthorURI] => http://ma.tt/
 *       [Title] => Hello Dolly
 *       [AuthorName] => Matt Mullenweg
 *       [...]
 *     )
 *   [name] => Hello Dolly
 *   [version_detected] => 1.6
 *   [file_name] => /tmp/plugins/hello.php
 *   [id] => w.org/plugins/hello-dolly
 *   [slug] => hello-dolly
 *   [new_version] => 1.7.2
 *   [package] => https://downloads.wordpress.org/plugin/hello-dolly.1.7.2.zip
 *   [url] => https://wordpress.org/plugins/hello-dolly/
 * )
 */
function vipgoci_wpcore_misc_get_addon_data_and_slugs_for_directory(
	string $local_git_repo,
	string $relative_path,
	array $plugin_file_extensions,
	array $theme_file_extensions,
	bool $process_subdirectories = true
) :array {
	$addons_found = vipgoci_wpcore_misc_scan_directory_for_addons(
		$local_git_repo,
		$relative_path,
		$plugin_file_extensions,
		$theme_file_extensions,
		$process_subdirectories
	);

	$addons_details = vipgoci_wpcore_api_determine_slug_and_other_for_addons(
		$addons_found
	);

	if ( null === $addons_details ) {
		return array();
	}

	/*
	 * Look through plugins found, assign slug found, version numbers, etc.
	 */
	$addons_found = vipgoci_wpcore_misc_assign_addon_fields(
		$addons_found,
		$addons_details
	);

	vipgoci_log(
		'Got plugin/theme information from directory scan and WordPress.org API request',
		array(
			'local_git_repo' => $local_git_repo,
			'relative_path'  => $relative_path,
			'addons_found'   => $addons_found,
		),
		2
	);

	return $addons_found;
}

/**
 * Returns a list of WordPress add-ons found in $known_addons that
 * cannot be associated with changes in pull requests. Attempts to
 * associate each changed file with an add-on, and returns
 * those that cannot be associated.
 *
 * @param array $options                        Options array for the program.
 * @param array $known_addons                   Array of paths to known add-ons (relative to repository base).
 * @param array $files_affected_by_commit_by_pr Files affected by commit by pull request (relative to repository base).
 *
 * @return Array Paths to add-ons that could not be associated with changed files.
 */
function vipgoci_wpcore_misc_get_addons_not_altered(
	array $options,
	array $known_addons,
	array $files_affected_by_commit_by_pr
) :array {
	$addons_matched = array();

	$changed_files = $files_affected_by_commit_by_pr['all'];

	$known_addon_base_paths = array();

	foreach ( $known_addons as $addon_path ) {
		$known_addon_base_paths[ dirname( $addon_path ) ] = $addon_path;
	}

	foreach ( $changed_files as $changed_file ) {
		if ( in_array( $changed_file, $known_addons, true ) ) {
			$addons_matched[ $changed_file ] = $changed_file;
			continue;
		}

		$changed_file_dirname = $changed_file;

		do {
			$changed_file_dirname = dirname( $changed_file_dirname );

			if ( in_array(
				$changed_file_dirname,
				$options['wpscan-api-paths'],
				true
			) ) {
				break;
			}

			if ( isset( $known_addon_base_paths[ $changed_file_dirname ] ) ) {
				$addons_matched[ $changed_file ] = $known_addon_base_paths[ $changed_file_dirname ];

				break;
			}
		} while ( str_contains( $changed_file_dirname, '/' ) );
	}

	return array_values(
		array_diff(
			$known_addons,
			array_values( $addons_matched )
		)
	);
}

