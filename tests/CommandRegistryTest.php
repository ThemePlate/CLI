<?php

/**
 * @package ThemePlate
 */

namespace Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use ThemePlate\CLI\CommandRegistry;
use ThemePlate\CLI\IndexCommand;

class CommandRegistryTest extends TestCase {
	public function testAddAndDump(): void {
		CommandRegistry::add( IndexCommand::class );

		$this->assertArrayHasKey( 'index.php', CommandRegistry::dump() );
	}


	public function testAddRejectsCommandWithoutName(): void {
		$this->expectException( \InvalidArgumentException::class );

		CommandRegistry::add( new Command() );
	}


	public function testResetClearsCommands(): void {
		CommandRegistry::add( IndexCommand::class );
		CommandRegistry::reset();

		$this->assertSame( array(), CommandRegistry::dump() );
	}


	protected function tearDown(): void {
		CommandRegistry::reset();

		parent::tearDown();
	}
}
