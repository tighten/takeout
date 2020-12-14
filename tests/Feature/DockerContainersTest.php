<?php

namespace Tests\Feature;

use App\Exceptions\DockerMissingException;
use App\Shell\Docker;
use App\Shell\Shell;
use Illuminate\Support\Collection;
use Mockery as M;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class DockerContainersTest extends TestCase
{
    function fakeStartedContainerList()
    {
        return <<<EOD
CONTAINER ID|NAMES|STATUS|PORTS|Base Alias|Full Alias
123456789abc|TO-meilisearch|Up 15 Minutes|7700:7700|meilisearch|meilisearch0.17
EOD;
    }

    function fakeStoppedContainerList()
    {
        return <<<EOD
CONTAINER ID|NAMES|STATUS|PORTS|Base Alias|Full Alias
123456789abc|TO-meilisearch|Exited (0) 2 days ago||meilisearch|meilisearch0.17
EOD;
    }

    /** @test */
    function it_formats_output_to_tables()
    {
        $this->mock(Shell::class, function ($mock) {
            $process = M::mock(Process::class);
            $process->shouldReceive('getOutput')->andReturn($this->fakeStartedContainerList());
            $mock->shouldReceive('execQuietly')->andReturn($process);
        });

        $output = app(Docker::class)->takeoutContainers();

        $expectedTable = [[
            'container_id' => '123456789abc',
            'names' => 'TO-meilisearch',
            'status' => 'Up 15 Minutes',
            'ports' => '7700:7700',
            'base_alias' => 'meilisearch',
            'full_alias' => 'meilisearch0.17'
        ],];

        $this->assertEquals($expectedTable, $output->toArray());
    }

    /** @test */
    function it_removes_container_that_is_already_stopped()
    {
        $this->mock(Shell::class, function ($mock) {
            $process = M::mock(Process::class);
            $process->shouldReceive('getOutput')->andReturn($this->fakeStoppedContainerList());
            $mock->shouldReceive('execQuietly')->andReturn($process);

            $process2 = M::mock(Process::class);
            $process2->shouldReceive('getOutput')->andReturn('');
            $mock->shouldReceive('exec')->andReturn($process2);
            $process2->shouldReceive('isSuccessful')->andReturn(true);
        });

        $output = app(Docker::class)->takeoutContainers();

        $expectedTable = [[
            'container_id' => $meilisearchId = '123456789abc',
            'names' => 'TO-meilisearch',
            'status' => 'Exited (0) 2 days ago',
            'ports' => '',
            'base_alias' => 'meilisearch',
            'full_alias' => 'meilisearch0.17'
        ],];

        $this->assertEquals($expectedTable, $output->toArray());

        app(Docker::class)->removeContainer($meilisearchId);
    }
}
