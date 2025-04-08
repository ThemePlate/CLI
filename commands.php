<?php

/**
 * @package ThemePlate
 */

namespace ThemePlate\CLI;

use Symfony\Component\Console\Command\Command;
use ThemePlate\CLI\CommandRegistry;

$files = glob( __DIR__ . '/src/*Command.php' );

if ( false === $files ) {
	return;
}

foreach ( $files as $file ) {
	/** @var class-string<Command> $class */
	$class = __NAMESPACE__ . '\\' . basename( $file, '.php' );

	CommandRegistry::add( new $class() );
}
