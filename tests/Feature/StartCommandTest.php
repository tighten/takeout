<?php

namespace Tests\Feature;

use App\Shell\Docker;
use Illuminate\Support\Collection;
use Tests\TestCase;

class StartCommandTest extends TestCase
{
    /** @test */
    function it_can_start_a_service_from_menu()
    {
        $services = Collection::make([
            [
            'container_id' => $containerId = '12345',
            'names' => 'TO--mysql--8.0.22--3306',
            'status' => 'Exited (0) 8 days ago',
            'ports' => '',
            'base_alias' => 'mysql',
            'full_alias' => 'mysql8.0',
            ],
        ]);

        $this->mock(Docker::class, function ($mock) use ($services, $containerId) {
            $mock->shouldReceive('isInstalled')->andReturn(true);
            $mock->shouldReceive('isDockerServiceRunning')->andReturn(true);
            $mock->shouldReceive('startableTakeoutContainers')->andReturn($services, new Collection);
            $mock->shouldReceive('startContainer')->with($containerId);
        });

        $this->artisan('start')
            ->expectsQuestion('Takeout containers to start', $containerId)
            ->assertExitCode(0);
    }

    /**
     * @test
     *
     * @testWith ["12345"]
     *           ["mysql"]
     *
     */
    function it_can_start_containers_by_name_or_id($arg)
    {
        $services = Collection::make([
            [
                'container_id' => $containerId = '12345',
                'names' => 'TO--mysql--8.0.22--3306',
                'status' => 'Exited (0) 8 days ago',
                'ports' => '',
                'base_alias' => 'mysql',
                'full_alias' => 'mysql8.0',
            ],
        ]);

        $this->mock(Docker::class, function ($mock) use ($services, $containerId) {
            $mock->shouldReceive('isInstalled')->andReturn(true);
            $mock->shouldReceive('isDockerServiceRunning')->andReturn(true);
            $mock->shouldReceive('startableTakeoutContainers')->andReturn($services, new Collection);
            $mock->shouldReceive('startContainer')->once()->with($containerId);
        });

        $this->artisan('start', ['containerId' => [$arg]])
            ->assertExitCode(0);
    }

    /** @test */
    function it_can_start_containers_by_name_when_there_are_multiple()
    {
        $services = Collection::make([
            [
                'container_id' => '12345',
                'names' => 'TO--mysql--8.0.22--3306',
                'status' => 'Exited (0) 8 days ago',
                'ports' => '',
                'base_alias' => 'mysql',
                'full_alias' => 'mysql8.0',
            ],
            [
                'container_id' => $secondContainerId = '67890',
                'names' => 'TO--mysql--8.0.20-3306',
                'status' => 'Exited (0) 8 days ago',
                'ports' => '',
                'base_alias' => 'mysql',
                'full_alias' => 'mysql8.0',
            ],
        ]);

        $this->mock(Docker::class, function ($mock) use ($services, $secondContainerId) {
            $mock->shouldReceive('isInstalled')->andReturn(true);
            $mock->shouldReceive('isDockerServiceRunning')->andReturn(true);
            $mock->shouldReceive('startableTakeoutContainers')->andReturn($services, new Collection);
            $mock->shouldReceive('startContainer')->with($secondContainerId)->once();
        });

        $this->artisan('start')
            ->expectsQuestion('Takeout containers to start', $secondContainerId)
            ->assertExitCode(0);
    }
}
