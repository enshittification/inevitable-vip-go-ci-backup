<?php
/**
 * Ensure all test files end with 'Test.php', unless exempt.
 *
 * @package Automattic/vip-go-ci
 */

declare(strict_types=1);

namespace Vipgoci\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Check if all tests are correctly named.
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
final class AllUnitTestsInplaceTest extends TestCase {
	/**
	 * Verify files.
	 *
	 * @return void
	 */
	public function testAllUnitTestsInPlace() :void {
		$files_arr = scandir( 'tests/unit' );

		/*
		 * Filter away any files that should be
		 * in the tests/unit directory, but should
		 * not be tested, i.e. supporting files.
		 *
		 * Also filter away files that end with 'Test.php', as
		 * they will be tested.
		 *
		 * This will result in an empty array when everything
		 * is accounted for.
		 */
		$files_arr = array_filter(
			$files_arr,
			function( $file_item ) {
				switch ( $file_item ) {
					case '.':
					case '..':
					case 'helper':
					case 'Skeleton.php':
						/*
						 * Remove those away from
						 * the resulting array, are
						 * supporting files.
						 */
						return false;
				}

				$file_item_end = strpos(
					$file_item,
					'Test.php'
				);

				if ( false !== $file_item_end ) {
					/*
					 * If the filename ends with 'Test.php',
					 * skip this file from the final result.
					 */
					return false;
				}

				/*
				 * Any other files,
				 * keep them in.
				 */
				return true;
			}
		);

		/*
		 * We should end with an empty array.
		 */
		$this->assertSame(
			0,
			count( $files_arr )
		);
	}
}
