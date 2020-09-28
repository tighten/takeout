<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Shell\Docker;
use App\Commands\DisableCommand;

class DisableCommandTest extends TestCase
{
    protected function setup(): void
    {
        parent::setUp();

        $this->mock(Docker::class, function ($mock) {
            $mock->shouldReceive('isInstalled')->andReturn(true);
            $mock->shouldReceive('isDockerServiceRunning')->andReturn(true);
            $mock->shouldReceive('takeoutContainers')->andReturn(collect([
                [
                    'container_id' => 'fake-id',
                    'names' => 'TO-mysql--latest--3306',
                ],
                [
                    'container_id' => 'fake-id-2',
                    'names' => 'TO-postgres--latest--5432',
                ],
            ]));

            $mock->shouldIgnoreMissing();
        });
    }

    /** @test */
    public function disable_menu_is_shown_when_no_service_in_input()
    {
        $this->mock(DisableCommand::class, function ($mock) {
            $mock->shouldReceive('showDisableServiceMenu')->andReturn(true);

            $mock->shouldIgnoreMissing();
        })->makePartial();

        // $this->artisan('disable');
        $this->markTestIncomplete('Need to ask Nuno if we can test laravel-console-menu');
    }

    /** @test */
    public function single_service_can_be_disabled()
    {
        $service = 'mysql';

        $this->mock(DisableCommand::class, function ($mock) {
            $mock->shouldReceive('disableByServiceName');
        });

        $this->artisan('disable ' . $service);
    }

    /** @test */
    public function multiple_services_can_be_disabled()
    {
        $mysql = 'mysql';
        $postgres = 'postgres';

        $this->mock(DisableCommand::class, function ($mock) {
            $mock->shouldReceive('disableByServiceName')->andReturn(null);
        });

        $this->artisan("disable {$mysql} {$postgres}");
    }

    /** @test */
    public function all_services_will_be_disabled_if_all_flag_passed()
    {
        $this->mock(DisableCommand::class, function ($mock) {
            $mock->shouldReceive('disableByContainerId');
        });

        $this->artisan('disable', [
            '--all' => true,
        ]);
    }
}
