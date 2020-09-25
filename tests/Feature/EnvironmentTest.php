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
    private function isLinux()
    {
        return PHP_OS_FAMILY === 'Linux';
    }

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

            $times = 1;
            if ($this->isLinux()) {
                $times = 2;
            }

            $mock->shouldReceive('execQuietly')->times($times)->andReturn($process);
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
            $process->shouldReceive('getOutput')->andReturn('');

            $times = 1;
            if ($this->isLinux()) {
                $times = 2;
            }

            $mock->shouldReceive('execQuietly')->times($times)->andReturn($process);
        });

        $environment = app(Environment::class);
        $this->assertTrue($environment->portIsAvailable(1234));
    }
}
