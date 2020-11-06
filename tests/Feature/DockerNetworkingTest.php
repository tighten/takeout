<?php

namespace Tests\Feature;

use App\Shell\DockerNetworking;
use App\Shell\Shell;
use Mockery as M;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class DockerNetworkingTest extends TestCase
{
    /** @test */
    function it_can_tell_whether_a_base_alias_exists()
    {
        $this->mock(Shell::class, function ($mock) {
            $process = M::mock(Process::class);
            $process->shouldReceive('getOutput')->andReturn("CONTAINER ID|NAMES\n48b5d370fbf9|TO--mysql--8.0.22--3306");
            $mock->shouldReceive('execQuietly')->andReturn($process);
        });

        $this->assertTrue(app(DockerNetworking::class)->baseAliasExists('mysql'));
    }

    /** @test */
    function it_can_tell_whether_a_base_alias_doesnt_exist()
    {
        $this->mock(Shell::class, function ($mock) {
            $process = M::mock(Process::class);
            $process->shouldReceive('getOutput')->andReturn("CONTAINER ID|NAMES\n");
            $mock->shouldReceive('execQuietly')->andReturn($process);
        });

        $this->assertFalse(app(DockerNetworking::class)->baseAliasExists('mysql'));
    }
}
