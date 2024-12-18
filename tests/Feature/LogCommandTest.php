<?php

namespace Tests\Feature;

use App\Shell\Docker;
use Illuminate\Support\Collection;
use Tests\TestCase;

class LogCommandTest extends TestCase
{
    /** @test */
    function it_can_access_logss_from_a_service_from_menu()
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
            $mock->shouldReceive('logContainer')->with($containerId);
        });

        $this->artisan('logs')
            ->expectsQuestion('Takeout containers logs', $containerId)
            ->assertExitCode(0);
    }

    /**
     * @test
     *
     * @testWith ["12345"]
     *           ["mysql"]
     *
     */
    public function it_can_access_logs_from_containers_by_service_name_or_id($arg)
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
            $mock->shouldReceive('logContainer')->with($containerId);
        });


        $this->artisan('logs', ['containerId' => $arg])
            ->assertExitCode(0);
    }
}
