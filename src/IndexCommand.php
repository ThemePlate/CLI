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
use Symfony\Component\Console\Question\ConfirmationQuestion;

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

		$destination = realpath( rtrim( $input->getArgument( 'path' ), '/\\' ) );

		if ( false === $destination || ! is_dir( $destination ) ) {
			return Command::FAILURE;
		}

		$relative = fn( string $path ): string => str_replace( $destination, '', $path );

		$maybe_add = function ( string $path ) use ( $relative, $output ): void {
			$file = fn( string $base ): string => $base . DIRECTORY_SEPARATOR . 'index.php';

			if ( ! file_exists( $file( $path ) ) ) {
				copy( $file( __DIR__ ), $file( $path ) );
				$output->writeln( 'Added .' . $relative( $file( $path ) ) );
			}
		};

		$files = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator(
				$destination,
				RecursiveDirectoryIterator::SKIP_DOTS
			),
			RecursiveIteratorIterator::SELF_FIRST
		);

		foreach ( $files as $fileinfo ) {
			if ( ! $fileinfo->isDir() ) {
				continue;
			}

			$directory = $fileinfo->getRealPath();

			if ( 0 === strpos( $relative( $directory ), DIRECTORY_SEPARATOR . '.' ) ) {
				continue;
			}

			if ( 0 === strpos( $relative( $directory ), DIRECTORY_SEPARATOR . 'node_modules' ) ) {
				continue;
			}

			if ( 0 === strpos( $relative( $directory ), DIRECTORY_SEPARATOR . 'vendor' ) ) {
				continue;
			}

			$maybe_add( $directory );
		}

		$maybe_add( $destination );

		return Command::SUCCESS;

	}

}
