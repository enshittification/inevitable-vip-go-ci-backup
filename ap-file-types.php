<?php
/**
 * Auto-approve based on file-types.
 *
 * @package Automattic/vip-go-ci
 */

declare(strict_types=1);

/**
 * Process all files in the PRs
 * involved with the commit specified.
 *
 * This function will add to an array
 * of auto-approvable files any files that
 * fit the criteria of having certain file-endings.
 * The allowable file-endings are specifiable
 * via the command-line.
 *
 * @param array $options                 Options needed.
 * @param array $auto_approved_files_arr Array of auto-approved files.
 */
function vipgoci_ap_file_types(
	array $options,
	array &$auto_approved_files_arr
) :void {
	vipgoci_runtime_measure( VIPGOCI_RUNTIME_START, 'ap_file_types' );

	vipgoci_log(
		'Doing auto-approval scanning based on file-types',
		array(
			'repo_owner'            => $options['repo-owner'],
			'repo_name'             => $options['repo-name'],
			'commit_id'             => $options['commit'],
			'autoapprove'           => $options['autoapprove'],
			'autoapprove-filetypes' => $options['autoapprove-filetypes'],
		)
	);

	$prs_implicated = vipgoci_github_prs_implicated(
		$options['repo-owner'],
		$options['repo-name'],
		$options['commit'],
		$options['token'],
		$options['branches-ignore'],
		$options['skip-draft-prs']
	);

	foreach ( $prs_implicated as $pr_item ) {
		$pr_diff = vipgoci_git_diffs_fetch(
			$options['local-git-repo'],
			$options['repo-owner'],
			$options['repo-name'],
			$options['token'],
			$pr_item->base->sha,
			$options['commit'],
			true, // Renamed files included.
			true, // Removed files included.
			true, // Permission changes included.
			null
		);

		/*
		 * Note: We will here loop through files
		 * that have been renamed, removed, had their
		 * permission changed, or had their contents
		 * modified -- and then we might auto-approve
		 * them (if their file-type is auto-approvable).
		 */

		foreach ( $pr_diff['files'] as
			$pr_diff_file_name => $pr_diff_contents
		) {
			/*
			 * If the file is already in the array
			 * of approved files, do not do anything.
			 */
			if ( isset(
				$auto_approved_files_arr[ $pr_diff_file_name ]
			) ) {
				continue;
			}

			$pr_diff_file_extension = vipgoci_file_extension_get(
				$pr_diff_file_name
			);

			/*
			 * Check if the extension of the file
			 * is in a list of auto-approvable
			 * file extensions.
			 */
			if ( in_array(
				$pr_diff_file_extension,
				$options['autoapprove-filetypes'],
				true
			) ) {
				$auto_approved_files_arr[ $pr_diff_file_name ]
					= 'autoapprove-filetypes';
			}
		}
	}

	vipgoci_runtime_measure( VIPGOCI_RUNTIME_STOP, 'ap_file_types' );
}

