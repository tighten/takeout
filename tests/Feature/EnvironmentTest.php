<?php

namespace Tests\Feature;

use App\Shell\Environment;
use App\Shell\Shell;
use LaravelZero\Framework\Commands\Command;
use Mockery as M;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class EnvironmentTest extends TestCase
{
    /** @test **/
    function it_detects_a_port_conflict()
    {
        app()->instance('console', M::mock(Command::class, function ($mock) {
            $mock->shouldIgnoreMissing();
        }));

        $this->mock(Shell::class, function ($mock) {
            $process = M::mock(Process::class);
            $process->shouldReceive('isSuccessful')->once()->andReturn(true);

            $mock->shouldReceive('execQuietly')->once()->andReturn($process);
        });

        $environment = app(Environment::Class);
        $this->assertFalse($environment->portIsAvailable(1234));
    }

    /** @test **/
    function it_detects_a_port_is_available()
    {
        app()->instance('console', M::mock(Command::class, function ($mock) {
            $mock->shouldIgnoreMissing();
        }));

        $this->mock(Shell::class, function ($mock) {
            $process = M::mock(Process::class);
            $process->shouldReceive('isSuccessful')->once()->andReturn(false);

            $mock->shouldReceive('execQuietly')->once()->andReturn($process);
        });

        $environment = app(Environment::Class);
        $this->assertTrue($environment->portIsAvailable(1234));
    }
}
