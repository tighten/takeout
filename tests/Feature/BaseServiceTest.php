<?php

namespace Tests\Feature;

use function app;
use Mockery as M;
use Tests\TestCase;
use App\Shell\Shell;
use App\Shell\Docker;
use App\Services\MeiliSearch;
use Symfony\Component\Process\Process;
use LaravelZero\Framework\Commands\Command;

class BaseServiceTest extends TestCase
{
    /** @test */
    public function it_generates_shortname()
    {
        $meilisearch = app(MeiliSearch::class);
        $this->assertEquals('meilisearch', $meilisearch->shortName());
    }

    /** @test */
    public function it_enables_services()
    {
        app()->instance('console', M::mock(Command::class, function ($mock) {
            $defaultPort = app(MeiliSearch::class)->defaultPort();
            $mock->shouldReceive('ask')->with('Which host port would you like meilisearch to use?', $defaultPort)->andReturn(7700);
            $mock->shouldReceive('ask')->with('What is the Docker volume name?', 'meili_data')->andReturn('meili_data');
            $mock->shouldReceive('ask')->with('Which tag (version) of meilisearch would you like to use?', 'latest')->andReturn('v1.1.1');
            $mock->shouldIgnoreMissing();
        }));

        $this->mock(Shell::class, function ($mock) {
            $process = M::mock(Process::class);
            $process->shouldReceive('isSuccessful')->andReturn(false);
            $process->shouldReceive('getOutput')->andReturn('');

            $mock->shouldReceive('execQuietly')->andReturn($process);
        });

        $this->mock(Docker::class, function ($mock) {
            $mock->shouldReceive('isInstalled')->andReturn(true);
            $mock->shouldReceive('imageIsDownloaded')->andReturn(true);
            $mock->shouldReceive('volumeIsAvailable')->andReturn(true);

            // This is the actual assertion
            $mock->shouldReceive('bootContainer')->with(['getmeili/meilisearch']);
        });

        $service = app(MeiliSearch::class); // Extends BaseService
        $service->enable();
    }
}
