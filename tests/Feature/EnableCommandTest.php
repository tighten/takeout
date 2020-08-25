<?php

namespace Tests\Feature;

use App\Exceptions\InvalidServiceShortnameException;
use App\Services\MeiliSearch;
use App\Shell\Docker;
use Tests\TestCase;

class EnableCommandTest extends TestCase
{
    /** @test */
    function it_finds_services_by_shortname()
    {
        $service = 'meilisearch';

        $this->mock(MeiliSearch::class, function ($mock) use ($service) {
            $mock->shouldReceive('enable')->once();
            $mock->shouldReceive('shortName')->once()->andReturn($service);
        });

        $this->mock(Docker::class, function ($mock) {
            $mock->shouldReceive('isInstalled')->andReturn(true);
            $mock->shouldReceive('isDockerServiceRunning')->andReturn(true);
        });

        $this->artisan('enable ' . $service);
    }

    /** @test */
    function it_displays_error_if_invalid_shortname_passed()
    {
        $this->mock(Docker::class, function ($mock) {
            $mock->shouldReceive('isInstalled')->andReturn(true);
            $mock->shouldReceive('isDockerServiceRunning')->andReturn(true);
        });

        $this->expectException(InvalidServiceShortnameException::class);
        $this->artisan('enable asdfasdfadsfasdfadsf')
            ->assertExitCode(0);
    }
}
