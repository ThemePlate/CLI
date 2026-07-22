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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Process\Process;

class IndexCommandTest extends TestCase {
	public function testExecute(): void {
		$path = $this->makePath();

		mkdir( $path . DIRECTORY_SEPARATOR . 'one' . DIRECTORY_SEPARATOR . 'two', 0777, true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_mkdir

		try {
			$tester = $this->executeCommand( $path );

			$tester->assertCommandIsSuccessful();
			$this->assertFileExists( $path . DIRECTORY_SEPARATOR . 'index.php' );
			$this->assertFileExists( $path . DIRECTORY_SEPARATOR . 'one' . DIRECTORY_SEPARATOR . 'index.php' );
			$this->assertFileExists( $path . DIRECTORY_SEPARATOR . 'one' . DIRECTORY_SEPARATOR . 'two' . DIRECTORY_SEPARATOR . 'index.php' );
		} finally {
			$this->removePath( $path );
		}
	}


	public function testExecuteFailsForMissingPath(): void {
		$path = $this->makePath();

		rmdir( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir

		$tester = $this->executeCommand( $path );

		$this->assertSame( Command::FAILURE, $tester->getStatusCode() );
	}


	public function testExecuteRejectsRootPath(): void {
		$path = $this->makePath();
		$cwd  = getcwd();

		$this->assertNotFalse( $cwd );
		chdir( $path );

		try {
			$tester = $this->executeCommand( DIRECTORY_SEPARATOR );

			$this->assertSame( Command::FAILURE, $tester->getStatusCode() );
			$this->assertFileDoesNotExist( $path . DIRECTORY_SEPARATOR . 'index.php' );
		} finally {
			chdir( $cwd );
			$this->removePath( $path );
		}
	}


	public function testExecuteDoesNotRunShellCodeFromPath(): void {
		$marker = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'themeplate-cli-marker-' . uniqid();
		$path   = $this->makePath( ';touch ' . $marker . ';' );

		try {
			$this->executeCommand( $path );

			$this->assertFileDoesNotExist( $marker );
		} finally {
			if ( file_exists( $marker ) ) {
				unlink( $marker ); // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
			}

			$this->removePath( $path );
		}
	}


	public function testExecuteHonorsGitIgnoreForPathWithSpaces(): void {
		$path = $this->makePath( ' with spaces' );

		mkdir( $path . DIRECTORY_SEPARATOR . 'ignored', 0777, true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_mkdir
		file_put_contents( $path . DIRECTORY_SEPARATOR . '.gitignore', "ignored/\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		( new Process( array( 'git', 'init' ), $path ) )->mustRun();

		try {
			$tester = $this->executeCommand( $path );

			$tester->assertCommandIsSuccessful();
			$this->assertFileDoesNotExist( $path . DIRECTORY_SEPARATOR . 'ignored' . DIRECTORY_SEPARATOR . 'index.php' );
		} finally {
			$this->removePath( $path );
		}
	}


	private function executeCommand( string $path ): CommandTester {
		$command = new IndexCommand();
		$tester  = new CommandTester( $command );

		( new Application() )->add( $command );
		$tester->execute( compact( 'path' ) );

		return $tester;
	}


	private function makePath( string $suffix = '' ): string {
		$path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'themeplate-cli-' . uniqid() . $suffix;

		mkdir( $path, 0777, true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_mkdir

		return $path;
	}


	private function removePath( string $path ): void {
		$files = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator(
				$path,
				RecursiveDirectoryIterator::SKIP_DOTS
			),
			RecursiveIteratorIterator::CHILD_FIRST
		);

		foreach ( $files as $fileinfo ) {
			if ( $fileinfo->isDir() ) {
				rmdir( $fileinfo->getPathname() ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir

				continue;
			}

			unlink( $fileinfo->getPathname() ); // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
		}

		rmdir( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir
	}
}
