<?php

namespace Tests\Feature;

use App\Shell\Docker;
use Illuminate\Support\Collection;
use Tests\TestCase;

class StopCommandTest extends TestCase
{
    /** @test */
    function it_can_stop_a_service_from_menu()
    {
        $services = Collection::make([
            [
            'container_id' => $containerId = '12345',
            'names' => 'TO--mysql--8.0.22--3306',
            'status' => 'Up 27 minutes',
            'ports' => '0.0.0.0:3306->3306/tcp, 33060/tcp',
            'base_alias' => 'mysql',
            'full_alias' => 'mysql8.0',
            ],
        ]);

        $this->mock(Docker::class, function ($mock) use ($services, $containerId) {
            $mock->shouldReceive('isInstalled')->andReturn(true);
            $mock->shouldReceive('isDockerServiceRunning')->andReturn(true);
            $mock->shouldReceive('activeTakeoutContainers')->andReturn($services, new Collection);
            $mock->shouldReceive('stopContainer')->with($containerId);
        });

        $this->artisan('stop')
            ->expectsQuestion('Takeout containers to stop', $containerId)
            ->assertExitCode(0);
    }

    /** @test */
    function it_can_stop_containers_by_service_name()
    {
        $services = Collection::make([
            [
                'container_id' => $containerId = '12345',
                'names' => 'TO--mysql--8.0.22--3306',
                'status' => 'Up 27 minutes',
                'ports' => '0.0.0.0:3306->3306/tcp, 33060/tcp',
                'base_alias' => 'mysql',
                'full_alias' => 'mysql8.0',
            ],
        ]);

        $this->mock(Docker::class, function ($mock) use ($services, $containerId) {
            $mock->shouldReceive('isInstalled')->andReturn(true);
            $mock->shouldReceive('isDockerServiceRunning')->andReturn(true);
            $mock->shouldReceive('activeTakeoutContainers')->andReturn($services, new Collection);
            $mock->shouldReceive('stopContainer')->with($containerId)->once();
        });

        $this->artisan('stop', ['containerId' => ['mysql']])
            ->assertExitCode(0);
    }

    /** @test */
    function it_can_stop_a_service_from_menu_when_there_are_multiple()
    {
        $services = Collection::make([
            [
                'container_id' => '12345',
                'names' => 'TO--mysql--8.0.22--3306',
                'status' => 'Up 27 minutes',
                'ports' => '0.0.0.0:3306->3306/tcp, 33060/tcp',
                'base_alias' => 'mysql',
                'full_alias' => 'mysql8.0',
            ],
            [
                'container_id' => $secondContainerId = '67890',
                'names' => 'TO--mysql--8.0.20--3306',
                'status' => 'Up 27 minutes',
                'ports' => '0.0.0.0:3306->3306/tcp, 33060/tcp',
                'base_alias' => 'mysql',
                'full_alias' => 'mysql8.0',
            ],
        ]);

        $this->mock(Docker::class, function ($mock) use ($services, $secondContainerId) {
            $mock->shouldReceive('isInstalled')->andReturn(true);
            $mock->shouldReceive('isDockerServiceRunning')->andReturn(true);
            $mock->shouldReceive('activeTakeoutContainers')->andReturn($services, new Collection);
            $mock->shouldReceive('stopContainer')->with($secondContainerId)->once();
        });

        $this->artisan('stop')
            ->expectsQuestion('Takeout containers to stop', $secondContainerId)
            ->assertExitCode(0);
    }
}
