<?php

/**
 * @package ThemePlate
 */

namespace Tests;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use ThemePlate\CLI\IndexCommand;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class IndexCommandTest extends TestCase {
	/** @return array<array<bool>> */
	public function forTestExecute(): array {
		return array(
			array( true ),
			array( false ),
		);
	}

	/**
	 * @dataProvider forTestExecute
	 */
	public function testExecute( bool $expected ): void {
		$command = new IndexCommand();
		$tester  = new CommandTester( $command );

		( new Application() )->add( $command );

		$path = './tester';

		if ( $expected ) {
			if ( ! is_dir( $path ) ) {
				mkdir( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_mkdir
			}

			for ( $i = 1; $i <= 2; $i++ ) {
				if ( ! is_dir( $path . DIRECTORY_SEPARATOR . $i ) ) {
					mkdir( $path . DIRECTORY_SEPARATOR . $i ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_mkdir
				}

				for ( $j = 1; $j <= 2; $j++ ) {
					if ( ! is_dir( $path . DIRECTORY_SEPARATOR . $i . DIRECTORY_SEPARATOR . $j ) ) {
						mkdir( $path . DIRECTORY_SEPARATOR . $i . DIRECTORY_SEPARATOR . $j ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_mkdir
					}
				}
			}
		}

		ob_start();
		$tester->execute( compact( 'path' ) );
		$this->assertSame( '', ob_get_clean() );

		if ( $expected ) {
			$tester->assertCommandIsSuccessful();
		} else {
			return;
		}

		$files = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator(
				$path,
				RecursiveDirectoryIterator::SKIP_DOTS
			),
			RecursiveIteratorIterator::CHILD_FIRST
		);

		foreach ( $files as $fileinfo ) {
			$command = ( $fileinfo->isDir() ? 'rmdir' : 'unlink' );

			$command( $fileinfo->getRealPath() );
		}

		rmdir( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir
	}
}
