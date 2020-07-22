<?php

namespace Tests\Feature;

use App\Exceptions\InvalidServiceShortnameException;
use App\Services\MeiliSearch;
use App\Shell\Docker;
use App\Shell\Shell;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Tests\TestCase;
use Mockery as M;

class InstallCommandTest extends TestCase
{
    /** @test */
    function it_finds_services_by_shortname()
    {
        $service = 'meilisearch';

        $this->mock(Shell::class, function ($mock) use ($service) {
            $process = M::mock(Process::class);
            $process->shouldReceive('getExitCode')->twice()->andReturn(0);
            $mock->shouldReceive('execQuietly')->once()->andReturn($process);
            $mock->shouldReceive('exec')->once()->with(M::on(function ($arg) use ($service) {
                return Str::contains($arg, $service);
            }))->andReturn($process);
        });

        $this->mock(Docker::class, function ($mock) {
            $mock->shouldReceive('isInstalled')->andReturn(true);
        });

        $this->artisan('install ' . $service)
             ->expectsQuestion('Which host port would you like this service to use?', '3306')
             ->expectsQuestion('Which tag (version) of this service would you like to use?', 'v0.12.0')
             ->expectsQuestion('What is the Docker volume name?', 'test')
             ->assertExitCode(0);
    }

    /** @test */
    function it_displays_error_if_invalid_shortname_passed()
    {
        $this->expectException(InvalidServiceShortnameException::class);
        $this->artisan('install asdfasdfadsfasdfadsf')
            ->assertExitCode(0);
    }
}
