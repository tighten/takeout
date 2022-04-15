<?php

namespace Tests\Feature;

use App\Shell\Docker;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use NunoMaduro\LaravelConsoleMenu\Menu;
use PHPUnit\Framework\Assert;
use Tests\TestCase;

class StopCommandTest extends TestCase
{
    function isWindows()
    {
        return PHP_OS_FAMILY === 'Windows';
    }

    function isLinux()
    {
        return PHP_OS_FAMILY === 'Linux';
    }

    /** @test */
    function it_can_stop_a_service_from_menu()
    {
        $services = Collection::make([
            [
            'container_id' => $containerId = '12345',
            'names' => $containerName = 'TO--mysql--8.0.22--3306',
            'status' => 'Up 27 minutes',
            'ports' => '0.0.0.0:3306->3306/tcp, 33060/tcp',
            'base_alias' => 'mysql',
            'full_alias' => 'mysql8.0',
            ],
        ]);

        $menuItems = [
            $mysql = $containerId . ' - ' . $containerName,
            '<info>Exit</>',
        ];

        $this->mock(Docker::class, function ($mock) use ($services, $containerId) {
            $mock->shouldReceive('isInstalled')->andReturn(true);
            $mock->shouldReceive('isDockerServiceRunning')->andReturn(true);
            $mock->shouldReceive('stoppableTakeoutContainers')->andReturn($services, new Collection);
            $mock->shouldReceive('stopContainer')->with($containerId);
        });

        if ($this->isWindows()) {
            $this->artisan('stop')
                ->expectsChoice('Takeout containers to stop', $mysql, $menuItems)
                ->assertExitCode(0);
        } else {
            $menuMock = $this->mock(Menu::class, function ($mock) use ($mysql) {
                $mock->shouldReceive('setTitleSeparator')->andReturnSelf();
                $mock->shouldReceive('addItems')->andReturnSelf();
                $mock->shouldReceive('addLineBreak')->andReturnSelf();
                $mock->shouldReceive('open')->andReturn($mysql)->once();
            });

            Command::macro(
                'menu',
                function (string $title) use ($menuMock, $services) {
                    Assert::assertEquals('Takeout containers to stop', $title);

                    return $menuMock;
                }
            );

            $this->artisan('stop');
        }
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
            $mock->shouldReceive('stoppableTakeoutContainers')->andReturn($services, new Collection);
            $mock->shouldReceive('stopContainer')->with($containerId)->once();
        });

        $this->artisan('stop', ['containerId' => ['mysql']])
            ->assertExitCode(0);
    }
}
