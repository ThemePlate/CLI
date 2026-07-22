<?php

/**
 * @package ThemePlate
 */

namespace ThemePlate\CLI;

use Symfony\Component\Console\Command\Command;

class CommandFactory {

	/** @var Command|class-string<Command> */
	protected $command;


	/** @param Command|class-string<Command> $command */
	public function __construct( $command ) {

		$this->command = $command;

	}


	public function __invoke(): Command {

		if ( $this->command instanceof Command ) {
			return $this->command;
		}

		$class = $this->command;

		return new $class();

	}

}
