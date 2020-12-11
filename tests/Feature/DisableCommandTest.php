<?php

namespace Tests\Feature;

use App\Shell\Docker;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use NunoMaduro\LaravelConsoleMenu\Menu;
use PHPUnit\Framework\Assert;
use Tests\TestCase;

class DisableCommandTest extends TestCase
{

    function isWindows()
    {
        return PHP_OS_FAMILY === 'Windows';
    }

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

        if ($this->isWindows()) {
            $disableableServices = $services->mapWithKeys(function ($item) {
                return [$item['container_id'] => $item['names']];
            })->toArray();

            array_push($disableableServices, '<info>Exit</>');

            $this->artisan('disable')
                ->expectsChoice('Takeout containers to disable', $postgressName, $disableableServices)
                ->assertExitCode(0);
        } else {
            $menuMock = $this->mock(Menu::class, function ($mock) use ($postgressId) {
                $mock->shouldReceive('addLineBreak')->andReturnSelf();
                $mock->shouldReceive('setPadding')->andReturnSelf();
                $mock->shouldReceive('open')->andReturn($postgressId)->once();
            });

            Command::macro(
                'menu',
                function (string $title, array $options) use ($services, $menuMock) {
                    Assert::assertEquals('Services to disable', $title);
                    Assert::assertEquals(
                        $services->mapWithKeys(function ($container) {
                            return [$container['container_id'] => $container['names']];
                        })->toArray(),
                        $options
                    );
                    return $menuMock;
                }
            );

            $this->artisan('disable');
        }
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
}
