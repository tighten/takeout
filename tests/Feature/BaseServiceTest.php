<?php

namespace Tests\Feature;

use App\Services\MeiliSearch;
use App\Shell\Docker;
use App\Shell\Shell;
use LaravelZero\Framework\Commands\Command;
use Mockery as M;
use Symfony\Component\Process\Process;
use Tests\TestCase;
use function app;

class BaseServiceTest extends TestCase
{
    /** @test */
    function it_generates_shortname()
    {
        $meilisearch = app(MeiliSearch::class);
        $this->assertEquals('meilisearch', $meilisearch->shortName());
    }

    /** @test */
    function it_enables_services()
    {
        app()->instance('console', M::mock(Command::class, function ($mock) {
            $defaultPort = app(MeiliSearch::class)->defaultPort();
            $mock->shouldReceive('ask')->with('Which host port would you like this service to use?', $defaultPort)->andReturn(7700);
            $mock->shouldReceive('ask')->with('Which tag (version) of this service would you like to use?', 'latest')->andReturn('v1.1.1');
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
        $service->enable();
    }
}
