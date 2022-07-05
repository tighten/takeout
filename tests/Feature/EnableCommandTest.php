<?php

namespace Tests\Feature;

use App\Commands\EnableCommand;
use App\Exceptions\InvalidServiceShortnameException;
use App\Services;
use App\Services\MeiliSearch;
use App\Services\PostgreSql;
use App\Shell\Docker;
use Illuminate\Console\Command;
use NunoMaduro\LaravelConsoleMenu\Menu;
use PHPUnit\Framework\Assert;
use Tests\TestCase;

class EnableCommandTest extends TestCase
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
    function it_can_enable_a_service_from_menu()
    {
        $services = [
            'meilisearch' => 'App\Services\MeiliSearch',
            $postgres = 'postgresql' => $fqcn = 'App\Services\PostgreSql',
        ];

        $menuItems = [
            '<fg=white;bg=blue;options=bold> DATABASE </>',
            'PostgreSQL',
            '<fg=white;bg=blue;options=bold> SEARCH </>',
            'MeiliSearch',
            '<info>Exit</>',
        ];

        $this->mock(Docker::class, function ($mock) {
            $mock->shouldReceive('isInstalled')->andReturn(true);
            $mock->shouldReceive('isDockerServiceRunning')->andReturn(true);
        });

        $this->mock(Services::class, function ($mock) use ($services, $fqcn) {
            $mock->shouldReceive('all')->andReturn($services);
            $mock->shouldReceive('get')->andReturn($fqcn);
        });

        $this->mock(PostgreSql::class, function ($mock) {
            $mock->shouldReceive('enable')->once();
        });

        if ($this->isWindows()) {
            $this->artisan('enable')
                ->expectsChoice('Takeout containers to enable', 'PostgreSQL', $menuItems)
                ->assertExitCode(0);
        } else {
            $menuMock = $this->mock(Menu::class, function ($mock) use ($postgres) {
                $mock->shouldReceive('setTitleSeparator')->andReturnSelf();
                $mock->shouldReceive('addStaticItem')->andReturnSelf()->times(4);
                $mock->shouldReceive('addOptions')->andReturnSelf();
                $mock->shouldReceive('addLineBreak')->andReturnSelf();
                $mock->shouldReceive('open')->andReturn($postgres)->once();
            });

            Command::macro(
                'menu',
                function (string $title) use ($menuMock) {
                    Assert::assertEquals('Takeout containers to enable', $title);

                    return $menuMock;
                }
            );

            $this->artisan('enable');
        }
    }

    /** @test */
    function it_can_navigate_a_submenu_in_windows()
    {
        if ($this->isWindows()) {
            $services = [
                'meilisearch' => 'App\Services\MeiliSearch',
                'postgresql' => 'App\Services\PostgreSql',
            ];

            $menuItems = [
                $category = '<fg=white;bg=blue;options=bold> DATABASE </>',
                'PostgreSQL',
                '<fg=white;bg=blue;options=bold> SEARCH </>',
                'MeiliSearch',
                $exit = '<info>Exit</>',
            ];

            $submenuItems = [
                '<fg=white;bg=blue;options=bold> DATABASE </>',
                'PostgreSQL',
                $back = '<info>Back</>',
                '<info>Exit</>',
            ];

            $this->mock(Services::class, function ($mock) use ($services) {
                $mock->shouldReceive('all')->andReturn($services);
            });

            $this->artisan('enable')
                    ->expectsChoice('Takeout containers to enable', $category, $menuItems)
                    ->expectsChoice('Takeout containers to enable', $back, $submenuItems)
                    ->expectsChoice('Takeout containers to enable', $exit, $menuItems)
                    ->assertExitCode(0);
        } else {
            $this->assertTrue(true);
        }
    }

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
    function it_finds_multiple_services()
    {
        $meilisearch = 'meilisearch';
        $postgres = 'postgres';

        $this->mock(MeiliSearch::class, function ($mock) use ($meilisearch) {
            $mock->shouldReceive('enable')->once();
            $mock->shouldReceive('shortName')->andReturn($meilisearch);
        });

        $this->mock(PostgreSql::class, function ($mock) use ($postgres) {
            $mock->shouldReceive('enable')->once();
            $mock->shouldReceive('shortName')->andReturn($postgres);
        });

        $this->mock(Docker::class, function ($mock) {
            $mock->shouldReceive('isInstalled')->andReturn(true);
            $mock->shouldReceive('isDockerServiceRunning')->andReturn(true);
        });

        $this->artisan("enable {$meilisearch} {$postgres}");
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

    /** @test */
    function it_removes_options()
    {
        $cli = explode(' ', "./takeout enable meilisearch postgresql mysql --default -- -e 'abc' --other-flag");

        $command = new EnableCommand;

        $this->assertEquals(['meilisearch', 'postgresql', 'mysql'], $command->removeOptions($cli));
    }

    /** @test */
    function it_extracts_passthrough_options()
    {
        $cli = explode(' ', "./takeout enable meilisearch postgresql mysql --default -- -e 'abc' --other-flag");

        $command = new EnableCommand;

        $this->assertEquals(['-e', "'abc'", '--other-flag'], $command->extractPassthroughOptions($cli));
    }
}
