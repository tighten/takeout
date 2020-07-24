<?php

namespace Tests\Feature;

use App\Services\MeiliSearch;
use App\Services\MySql;
use App\Shell\Docker;
use App\Shell\DockerTags;
use App\Shell\Shell;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;
use Mockery as M;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class BaseServiceTest extends TestCase
{
    /** @test */
    function it_generates_shortname()
    {
        $meilisearch = app(MeiliSearch::class);
        $this->assertEquals('meilisearch', $meilisearch->shortName());
    }

    /** @test */
    function it_installs_services()
    {
        app()->instance('console', M::mock(Command::class, function ($mock) {
            $mock->shouldIgnoreMissing();
        }));

        $this->mock(Shell::class, function ($mock) {
            $process = M::mock(Process::class);
            $process->shouldReceive('isSuccessful')->andReturn(false);

            $mock->shouldReceive('execQuietly')->andReturn($process);
        });

        $this->mock(Docker::class, function ($mock) {
            $mock->shouldReceive('isInstalled')->andReturn(true);
            $mock->shouldReceive('imageIsDownloaded')->andReturn(true);

            // This is the actual assertion
            $mock->shouldReceive('bootContainer')->with(['getmeili/meilisearch']);
        });

        $service = app(MeiliSearch::Class); // Extends BaseService
        $service->install();
    }
}
