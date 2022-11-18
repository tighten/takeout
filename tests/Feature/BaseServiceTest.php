<?php

namespace Tests\Feature;

use App\Services\MeiliSearch;
use App\Services\PostgreSql;
use App\Shell\Docker;
use App\Shell\Shell;
use LaravelZero\Framework\Commands\Command;
use Mockery as M;
use Symfony\Component\Process\Process;
use Tests\Support\FakeService;
use Tests\TestCase;

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
        $service = app(MeiliSearch::class);

        app()->instance('console', M::mock(Command::class, function ($mock) use ($service) {
            $defaultPort = $service->defaultPort();
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

        $this->mock(Docker::class, function ($mock) use ($service) {
            $mock->shouldReceive('isInstalled')->andReturn(true);
            $mock->shouldReceive('imageIsDownloaded')->andReturn(true);
            $mock->shouldReceive('volumeIsAvailable')->andReturn(true);

            // This is the actual assertion
            $mock->shouldReceive('bootContainer')->with(
                $service->sanitizeDockerRunTemplate($service->dockerRunTemplate()),
                [
                    'organization' => 'getmeili',
                    'image_name' => 'meilisearch',
                    'port' => 7700,
                    'tag' => 'v1.1.1',
                    'volume' => 'meili_data',
                    'container_name' => 'TO--meilisearch--v1.1.1--7700',
                    'alias' => 'meilisearch1.1',
                ]
            )->once();
        });

        $service = app(MeiliSearch::class); // Extends BaseService
        $service->enable();
    }

    /** @test */
    public function it_can_receive_a_custom_image_in_the_tag()
    {
        $service = app(PostgreSql::class);

        app()->instance('console', M::mock(Command::class, function ($mock) use ($service) {
            $defaultPort = $service->defaultPort();
            $mock->shouldReceive('ask')->with('Which host port would you like postgres to use?', $defaultPort)->andReturn(5432);
            $mock->shouldReceive('ask')->with('Which tag (version) of postgres would you like to use?', 'latest')->andReturn('timescale/timescaledb:latest-pg12');
            $mock->shouldReceive('ask')->with('What is the Docker volume name?', 'postgres_data')->andReturn('postgres_data');
            $mock->shouldIgnoreMissing();
        }));

        $this->mock(Shell::class, function ($mock) {
            $process = M::mock(Process::class);
            $process->shouldReceive('isSuccessful')->andReturn(false);
            $process->shouldReceive('getOutput')->andReturn('');

            $mock->shouldReceive('execQuietly')->andReturn($process);
        });

        $this->mock(Docker::class, function ($mock) use ($service) {
            $mock->shouldReceive('isInstalled')->andReturn(true);
            $mock->shouldReceive('imageIsDownloaded')->andReturn(true);
            $mock->shouldReceive('volumeIsAvailable')->andReturn(true);

            // This is the actual assertion
            $mock->shouldReceive('bootContainer')->with(
                $service->sanitizeDockerRunTemplate($service->dockerRunTemplate()),
                [
                    'organization' => 'timescale',
                    'image_name' => 'timescaledb',
                    'port' => 5432,
                    'tag' => 'latest-pg12',
                    'volume' => 'postgres_data',
                    'root_password' => null,
                    'container_name' => 'TO--postgresql--latest-pg12--5432',
                    'alias' => 'postgresql-latest-pg12',
                ]
            )->once();
        });

        $service = app(PostgreSql::class); // Extends BaseService
        $service->enable();
    }

    /** @test */
    public function it_can_receive_container_arguments_via_passthrough()
    {
        $service = app(FakeService::class);
        $passthroughOptions = ['-h=127.0.0.1', '-usome-user'];

        app()->instance('console', M::mock(Command::class, function ($mock) use ($service) {
            $defaultPort = $service->defaultPort();
            $mock->shouldReceive('ask')->with('Which host port would you like _test_image to use?', $defaultPort)->andReturn(12345);
            $mock->shouldReceive('ask')->with('Which tag (version) of _test_image would you like to use?', 'latest')->andReturn('latest');
            $mock->shouldIgnoreMissing();
        }));

        $this->mock(Shell::class, function ($mock) {
            $process = M::mock(Process::class);
            $process->shouldReceive('isSuccessful')->andReturn(false);
            $process->shouldReceive('getOutput')->andReturn('');

            $mock->shouldReceive('execQuietly')->andReturn($process);
        });

        $this->mock(Docker::class, function ($mock) use ($service, $passthroughOptions) {
            $mock->shouldReceive('isInstalled')->andReturn(true);
            $mock->shouldReceive('imageIsDownloaded')->andReturn(true);
            $mock->shouldReceive('volumeIsAvailable')->andReturn(true);

            // This is the actual assertion
            $mock->shouldReceive('bootContainer')->with(
                join(' ', [
                    $service->sanitizeDockerRunTemplate($service->dockerRunTemplate()),
                    $service->buildPassthroughOptionsString($passthroughOptions),
                ]),
                [
                    'organization' => 'tighten',
                    'image_name' => '_test_image',
                    'port' => 12345,
                    'tag' => 'latest',
                    'container_name' => 'TO--fakeservice--latest--12345',
                    'alias' => 'fakeservice-latest',
                ]
            )->once();
        });

        // We need to create a new instance of the service so it can use the mocked objects...
        $service = app(FakeService::class);
        $service->enable(passthroughOptions: $passthroughOptions);
    }

    /** @test */
    public function it_accepts_run_options_and_passthrough_options()
    {
        $service = app(FakeService::class);
        $passthroughOptions = ['-h=127.0.0.1', '-usome-user'];
        $runOptions = '--restart unless-stopped -e "LOREM=IPSUM"';

        app()->instance('console', M::mock(Command::class, function ($mock) use ($service) {
            $defaultPort = $service->defaultPort();
            $mock->shouldReceive('ask')->with('Which host port would you like _test_image to use?', $defaultPort)->andReturn(12345);
            $mock->shouldReceive('ask')->with('Which tag (version) of _test_image would you like to use?', 'latest')->andReturn('latest');
            $mock->shouldIgnoreMissing();
        }));

        $this->mock(Shell::class, function ($mock) {
            $process = M::mock(Process::class);
            $process->shouldReceive('isSuccessful')->andReturn(false);
            $process->shouldReceive('getOutput')->andReturn('');

            $mock->shouldReceive('execQuietly')->andReturn($process);
        });

        $this->mock(Docker::class, function ($mock) use ($service, $runOptions, $passthroughOptions) {
            $mock->shouldReceive('isInstalled')->andReturn(true);
            $mock->shouldReceive('imageIsDownloaded')->andReturn(true);
            $mock->shouldReceive('volumeIsAvailable')->andReturn(true);

            // This is the actual assertion
            $mock->shouldReceive('bootContainer')->with(
                join(' ', [
                    $runOptions,
                    $service->sanitizeDockerRunTemplate($service->dockerRunTemplate()),
                    $service->buildPassthroughOptionsString($passthroughOptions),
                ]),
                [
                    'organization' => 'tighten',
                    'image_name' => '_test_image',
                    'port' => 12345,
                    'tag' => 'latest',
                    'container_name' => 'TO--fakeservice--latest--12345',
                    'alias' => 'fakeservice-latest',
                ]
            )->once();
        });

        // We need to create a new instance of the service so it can use the mocked objects...
        $service = app(FakeService::class);
        $service->enable(passthroughOptions: $passthroughOptions, runOptions: $runOptions);
    }
}
