<?php

/**
 * @package ThemePlate
 */

namespace ThemePlate\CLI;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class IndexCommand extends Command {

	// phpcs:disable WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase
	/** @var string */
	protected static $defaultName = 'index.php';

	/** @var string */
	protected static $defaultDescription = 'Recursively add index.php';
	// phpcs:enable WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase


	protected function configure(): void {

		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$this->setName( self::$defaultName );
		$this->setDescription( self::$defaultDescription );
		// phpcs:enable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$this->addArgument( 'path', InputArgument::OPTIONAL, 'Specify the start path', '.' );

	}


	protected function execute( InputInterface $input, OutputInterface $output ): int {

		$path = rtrim( $input->getArgument( 'path' ), '/\\' );

		if ( '' === $path ) {
			return Command::FAILURE;
		}

		$destination = realpath( $path );

		if ( false === $destination || ! is_dir( $destination ) ) {
			return Command::FAILURE;
		}

		$relative = fn( string $file ): string => substr( $file, strlen( $destination ) );

		$maybe_add = function ( string $path ) use ( $relative, $output ): bool {
			$file = fn( string $base ): string => $base . DIRECTORY_SEPARATOR . 'index.php';

			if ( file_exists( $file( $path ) ) ) {
				return true;
			}

			if ( ! copy( $file( __DIR__ ), $file( $path ) ) ) {
				$output->writeln( 'Unable to add .' . $relative( $file( $path ) ) );

				return false;
			}

			$output->writeln( 'Added .' . $relative( $file( $path ) ) );

			return true;
		};

		$files = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator(
				$destination,
				RecursiveDirectoryIterator::SKIP_DOTS
			),
			RecursiveIteratorIterator::SELF_FIRST
		);

		$process = new Process(
			array( 'git', 'ls-files', '--others', '--directory', '--ignored', '--exclude-standard' ),
			$destination
		);
		$ignores = array();
		$success = true;

		if ( 0 === $process->run() ) {
			$ignores = array_filter(
				array_map(
					fn( string $file ): string => str_replace( array( '/', '\\' ), DIRECTORY_SEPARATOR, $file ),
					explode( "\n", trim( $process->getOutput() ) )
				),
				fn( string $file ): bool => substr( $file, -1 ) === DIRECTORY_SEPARATOR
			);

			array_unshift( $ignores, '.git' . DIRECTORY_SEPARATOR );
		}

		foreach ( $files as $fileinfo ) {
			if ( ! $fileinfo->isDir() ) {
				continue;
			}

			$directory = $fileinfo->getRealPath();

			if ( false === $directory ) {
				continue;
			}

			if ( array() !== $ignores ) {
				foreach ( $ignores as $ignore ) {
					if ( 0 === strpos( ltrim( $relative( $directory ), DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR, $ignore ) ) {
						continue 2;
					}
				}

				$success = $maybe_add( $directory ) && $success;

				continue;
			}

			if ( false !== strpos( $relative( $directory ), DIRECTORY_SEPARATOR . '.' ) ) {
				continue;
			}

			if ( false !== strpos( $relative( $directory ), DIRECTORY_SEPARATOR . 'node_modules' ) ) {
				continue;
			}

			if ( false !== strpos( $relative( $directory ), DIRECTORY_SEPARATOR . 'vendor' ) ) {
				continue;
			}

			$success = $maybe_add( $directory ) && $success;
		}

		$success = $maybe_add( $destination ) && $success;

		return $success ? Command::SUCCESS : Command::FAILURE;

	}

}
