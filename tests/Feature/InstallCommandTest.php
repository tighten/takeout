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

        $this->mock(MeiliSearch::class, function ($mock) use ($service) {
            $mock->shouldReceive('install')->once();
            $mock->shouldReceive('shortName')->once()->andReturn($service);
        });

        $this->mock(Docker::class, function ($mock) {
            $mock->shouldReceive('isInstalled')->andReturn(true);
        });

        $this->artisan('install ' . $service);
    }

    /** @test */
    function it_displays_error_if_invalid_shortname_passed()
    {
        $this->mock(Docker::class, function ($mock) {
            $mock->shouldReceive('isInstalled')->andReturn(true);
        });

        $this->expectException(InvalidServiceShortnameException::class);
        $this->artisan('install asdfasdfadsfasdfadsf')
            ->assertExitCode(0);
    }
}
