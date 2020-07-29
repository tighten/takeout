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
CONTAINER ID,NAMES,STATUS
123456789abc,TO-meilisearch,Up 15 Minutes
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
            ['CONTAINER ID', 'NAMES', 'STATUS'],
            ['123456789abc', 'TO-meilisearch', 'Up 15 Minutes'],
        ];

        $this->assertEquals($expectedTable, $output);
    }
}
