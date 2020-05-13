<?php

namespace Tests\Feature;

use App\Shell\Docker;
use App\Shell\Shell;
use Mockery as M;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class DockerCheckTest extends TestCase
{
    /** @test */
    function the_check_returns_true_if_docker_is_installed()
    {
        $process = M::mock(Process::class);
        $process->shouldReceive('getExitCode')->andReturn(0);

        $shell = $this->mock(Shell::class);
        $shell->shouldReceive('exec')->andReturn($process);

        app()->instance(Shell::class, $shell);

        $this->assertTrue(app(Docker::class)->isInstalled());
    }

    /** @test */
    function the_check_returns_false_if_docker_is_not_installed()
    {
        $process = M::mock(Process::class);
        $process->shouldReceive('getExitCode')->andReturn(127);

        $shell = $this->mock(Shell::class);
        $shell->shouldReceive('exec')->andReturn($process);

        app()->instance(Shell::class, $shell);

        $this->assertFalse(app(Docker::class)->isInstalled());
    }
}
