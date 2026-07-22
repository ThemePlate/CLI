<?php

/**
 * @package ThemePlate
 */

namespace ThemePlate\CLI;

use Symfony\Component\Console\Command\Command;

class CommandRegistry {

	/** @var array<string, CommandFactory> */
	protected static array $commands = array();


	/** @param Command|string $command */
	public static function add( $command ): void {

		if ( $command instanceof Command ) {
			$name = $command->getName();
		} elseif ( is_subclass_of( $command, Command::class ) ) {
			$name = $command::getDefaultName();
		} else {
			throw new \InvalidArgumentException( 'Command class must extend Symfony Command.' );
		}

		if ( null === $name ) {
			throw new \InvalidArgumentException( 'Command name must be set.' );
		}

		self::$commands[ $name ] = new CommandFactory( $command );

	}


	public static function reset(): void {

		self::$commands = array();

	}


	/** @return array<string, CommandFactory> */
	public static function dump(): array {

		return self::$commands;

	}

}
