<?php

namespace Tests\Feature;

use App\Shell\Docker;
use App\Shell\Shell;
use Mockery as M;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class DockerInfoTest extends TestCase
{
    /** @test */
    function the_installed_check_returns_true_if_docker_is_installed()
    {
        $this->mock(Shell::class, function ($mock) {
            $process = M::mock(Process::class);
            $process->shouldReceive('getExitCode')->andReturn(0);
            $mock->shouldReceive('exec')->andReturn($process);
        });

        $this->assertTrue(app(Docker::class)->isInstalled());
    }

    /** @test */
    function the_installed_check_returns_false_if_docker_is_not_installed()
    {
        $this->mock(Shell::class, function ($mock) {
            $process = M::mock(Process::class);
            $process->shouldReceive('getExitCode')->andReturn(127);
            $mock->shouldReceive('exec')->andReturn($process);
        });

        $this->assertFalse(app(Docker::class)->isInstalled());
    }
}
