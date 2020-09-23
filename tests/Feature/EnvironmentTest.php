<?php

namespace Tests\Feature;

use Mockery as M;
use Tests\TestCase;
use App\Shell\Shell;
use App\Shell\Environment;
use Symfony\Component\Process\Process;
use LaravelZero\Framework\Commands\Command;

class EnvironmentTest extends TestCase
{
    /** @test **/
    public function it_detects_a_port_conflict()
    {
        app()->instance('console', M::mock(Command::class, function ($mock) {
            $mock->shouldIgnoreMissing();
        }));

        $this->mock(Shell::class, function ($mock) {
            $process = M::mock(Process::class);
            $process->shouldReceive('isSuccessful')->once()->andReturn(true);
            $process->shouldReceive('getOutput')->andReturn('');

            $mock->shouldReceive('execQuietly')->twice()->andReturn($process);
        });

        $environment = app(Environment::class);
        $this->assertFalse($environment->portIsAvailable(1234));
    }

    /** @test **/
    public function it_detects_a_port_is_available()
    {
        app()->instance('console', M::mock(Command::class, function ($mock) {
            $mock->shouldIgnoreMissing();
        }));

        $this->mock(Shell::class, function ($mock) {
            $process = M::mock(Process::class);
            $process->shouldReceive('isSuccessful')->once()->andReturn(false);
            $process->shouldReceive('getOutput')->andReturn('microsoft');

            $mock->shouldReceive('execQuietly')->twice()->andReturn($process);
        });

        $environment = app(Environment::class);
        $this->assertTrue($environment->portIsAvailable(1234));
    }
}
