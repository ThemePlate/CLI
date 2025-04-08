<?php

/**
 * @package ThemePlate
 */

namespace ThemePlate\CLI;

use Symfony\Component\Console\Command\Command;

class CommandRegistry {

	/** @var array<string, CommandFactory> */
	protected static array $commands = array();


	public static function add( Command $command ): void {

		self::$commands[ $command->getName() ] = new CommandFactory( $command );

	}


	/** @return array<string, CommandFactory> */
	public static function dump(): array {

		return self::$commands;

	}

}
