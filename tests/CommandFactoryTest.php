<?php

/**
 * @package ThemePlate
 */

namespace Tests;

use PHPUnit\Framework\TestCase;
use ThemePlate\CLI\CommandFactory;
use ThemePlate\CLI\IndexCommand;

class CommandFactoryTest extends TestCase {
	public function testConstructAndInvoke(): void {
		try {
			$factory = new CommandFactory( IndexCommand::class );
		} catch ( \TypeError $error ) {
			$factory = null;
		}

		$this->assertInstanceOf( CommandFactory::class, $factory );

		$first  = $factory();
		$second = $factory();

		$this->assertInstanceOf( IndexCommand::class, $first );
		$this->assertInstanceOf( IndexCommand::class, $second );
		$this->assertNotSame( $first, $second );
	}
}
