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
	$class = __NAMESPACE__ . '\\' . basename( $file, '.php' );

	if ( ! is_subclass_of( $class, Command::class ) || ! ( new \ReflectionClass( $class ) )->isInstantiable() ) {
		continue;
	}

	CommandRegistry::add( $class );
}
