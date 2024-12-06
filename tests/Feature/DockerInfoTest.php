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
            $process->shouldReceive('isSuccessful')->andReturn(true);
            $mock->shouldReceive('execQuietly')->andReturn($process);
        });

        $this->assertTrue(app(Docker::class)->isInstalled());
    }

    /** @test */
    function the_installed_check_returns_false_if_docker_is_not_installed()
    {
        $this->mock(Shell::class, function ($mock) {
            $process = M::mock(Process::class);
            $process->shouldReceive('isSuccessful')->andReturn(false);
            $mock->shouldReceive('execQuietly')->andReturn($process);
        });

        $this->assertFalse(app(Docker::class)->isInstalled());
    }
}
