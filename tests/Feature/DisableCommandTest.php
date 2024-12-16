<?php

namespace Tests\Feature;

use App\Shell\Docker;
use Illuminate\Support\Collection;
use Tests\TestCase;

class DisableCommandTest extends TestCase
{
    /** @test */
    function it_can_disable_a_service_from_menu()
    {
        $services = Collection::make([
            [
                'container_id' =>  $postgressId =  '1234',
                'names' => $postgressName = 'postgress',
            ],
            [
                'container_id' =>'12345',
                'names' => 'meilisearch',
            ],
        ]);

        $this->mock(Docker::class, function ($mock) use ($services, $postgressId) {
            $mock->shouldReceive('isInstalled')->andReturn(true);
            $mock->shouldReceive('isDockerServiceRunning')->andReturn(true);
            $mock->shouldReceive('takeoutContainers')->andReturn($services);

            $mock->shouldReceive('attachedVolumeName')
                ->with($postgressId)->andReturnNull()->once();
            $mock->shouldReceive('removeContainer')
                ->with($postgressId)->once();
        });

        $this->artisan('disable')
            ->expectsQuestion('Takeout containers to disable', $postgressId)
            ->assertExitCode(0);
    }

    /** @test */
    function it_can_disable_a_service_without_volume()
    {
        $services = Collection::make([
            [
                'container_id' => $postgressId = '1234',
                'names' => 'postgress',
            ],
        ]);

        $this->mock(Docker::class, function ($mock) use ($services, $postgressId) {
            $mock->shouldReceive('isInstalled')->andReturn(true);
            $mock->shouldReceive('isDockerServiceRunning')->andReturn(true);
            $mock->shouldReceive('takeoutContainers')->andReturn($services);

            $mock->shouldReceive('attachedVolumeName')
                ->with($postgressId)->andReturnNull()->once();
            $mock->shouldReceive('removeContainer')
                ->with($postgressId)->once();

            $mock->shouldReceive('allContainers')
                ->andReturn(Collection::make([
                    [
                        'container_id' => '1111',
                        'names' => 'someNonTakeoutContainer',
                    ],
                ]))->once();
        });

        $this->artisan('disable postgress');
    }

    /** @test */
    function it_can_disable_a_service_with_volume()
    {
        $services = Collection::make([
            [
                'container_id' => $postgressId = '1234',
                'names' => 'postgress',
            ],
        ]);

        $this->mock(Docker::class, function ($mock) use ($services, $postgressId) {
            $mock->shouldReceive('isInstalled')->andReturn(true);
            $mock->shouldReceive('isDockerServiceRunning')->andReturn(true);
            $mock->shouldReceive('takeoutContainers')->andReturn($services);

            $mock->shouldReceive('attachedVolumeName')
                ->with($postgressId)->andReturnNull()->once();
            $mock->shouldReceive('removeContainer')
                ->with($postgressId)->once();

            $mock->shouldReceive('allContainers')
                ->andReturn(Collection::make([
                    [
                        'container_id' => '1111',
                        'names' => 'someNonTakeoutContainer',
                    ],
                ]))->once();
        });

        $this->artisan('disable postgress');
    }

    /** @test */
    function it_can_disable_multiple_services()
    {
        $services = Collection::make([
            [
                'container_id' => $postgressId = '1234',
                'names' => 'postgress',
            ],
            [
                'container_id' => $meilisearchId = '12345',
                'names' => 'meilisearch',
            ],
        ]);

        $this->mock(Docker::class, function ($mock) use ($services, $postgressId, $meilisearchId) {
            $mock->shouldReceive('isInstalled')->andReturn(true);
            $mock->shouldReceive('isDockerServiceRunning')->andReturn(true);
            $mock->shouldReceive('takeoutContainers')->andReturn($services);

            $mock->shouldReceive('attachedVolumeName')
                ->with($postgressId)->andReturnNull()->once();
            $mock->shouldReceive('removeContainer')
                ->with($postgressId)->once();

            $mock->shouldReceive('attachedVolumeName')
                ->with($meilisearchId)->andReturnNull()->once();
            $mock->shouldReceive('removeContainer')
                ->with($meilisearchId)->once();
        });

        $this->artisan('disable postgress meilisearch');
    }

    /** @test */
    function it_will_try_to_stop_docker_service_if_no_containers_are_running()
    {
        $services = Collection::make([
            [
                'container_id' =>  $postgressId =  '1234',
                'names' => 'postgress',
            ],
        ]);

        $this->mock(Docker::class, function ($mock) use ($services, $postgressId) {
            $mock->shouldReceive('isInstalled')->andReturn(true);
            $mock->shouldReceive('isDockerServiceRunning')->andReturn(true);
            $mock->shouldReceive('takeoutContainers')->andReturn($services);

            $mock->shouldReceive('attachedVolumeName')
                ->with($postgressId)->andReturnNull()->once();
            $mock->shouldReceive('removeContainer')
                ->with($postgressId)->once();

            $mock->shouldReceive('allContainers')->andReturn(new Collection)->once();

            $mock->shouldReceive('stopDockerService')->once();
        });

        $this->artisan('disable postgress')
            ->expectsConfirmation('No containers are running. Turn off Docker?', 'Yes')
            ->assertExitCode(0);
    }
}
