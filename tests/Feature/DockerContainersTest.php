<?php

namespace Tests\Feature;

use App\Shell\Docker;
use App\Shell\Shell;
use Mockery as M;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class DockerContainersTest extends TestCase
{
    function fakeContainerList()
    {
        return <<<EOD
CONTAINER ID        NAMES
123456789abc        TO-meilisearch
EOD;
    }

    /** @test */
    function it_formats_output_to_tables()
    {
        $this->mock(Shell::class, function ($mock) {
            $process = M::mock(Process::class);
            $process->shouldReceive('getOutput')->andReturn($this->fakeContainerList());
            $mock->shouldReceive('execQuietly')->andReturn($process);
        });

        $output = app(Docker::class)->containers();

        $expectedTable = [
            ['CONTAINER ID', 'NAMES'],
            ['123456789abc', 'TO-meilisearch'],
        ];

        $this->assertEquals($expectedTable, $output);
    }
}
