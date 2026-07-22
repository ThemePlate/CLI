<?php

/**
 * @package ThemePlate
 */

namespace Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class BinThemeplateTest extends TestCase {
	public function testRunsOutsideProjectDirectory(): void {
		$process = new Process(
			array( PHP_BINARY, dirname( __DIR__ ) . '/bin/themeplate', '--version' ),
			sys_get_temp_dir()
		);

		$process->run();

		$this->assertTrue( $process->isSuccessful(), $process->getErrorOutput() );
	}
}
