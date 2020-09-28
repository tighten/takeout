<?php

namespace Tests\Feature;

use App\Commands\DisableCommand;
use App\Shell\Docker;
use Tests\TestCase;

class DisableCommandTest extends TestCase
{
    protected function setup(): void
    {
        parent::setUp();

        $this->mock(Docker::class, function ($mock) {
            $mock->shouldReceive('isInstalled')->andReturn(true);
            $mock->shouldReceive('isDockerServiceRunning')->andReturn(true);
            $mock->shouldIgnoreMissing();
        });
    }

    /** @test */
    public function disable_menu_is_shown_when_no_service_in_input()
    {
        $this->mock(DisableCommand::class, function ($mock) {
            $mock->shouldReceive('disableableServices')->andReturn([
                'fake-id' => 'TO-mysql--latest--3306',
            ]);
            $mock->shouldReceive('showDisableServiceMenu');
            $mock->shouldIgnoreMissing();
        });

        $this->artisan('disable');
    }

    /** @test */
    public function single_service_can_be_disabled()
    {
        $service = 'mysql';

        $this->mock(DisableCommand::class, function ($mock) use ($service) {
            $mock->shouldReceive('disableByServiceName');
            $mock->shouldReceive('disableableServices')->andReturn([
                'fake-id' => "TO-{$service}--latest--3306",
            ]);
            $mock->shouldIgnoreMissing();
        });

        $this->artisan('disable ' . $service);
    }

    /** @test */
    public function multiple_services_can_be_disabled()
    {
        $mysql = 'mysql';
        $postgres = 'postgres';

        $this->mock(DisableCommand::class, function ($mock) use ($mysql, $postgres) {
            $mock->shouldReceive('disableByServiceName')->andReturn(null);
            $mock->shouldReceive('disableableServices')->andReturn([
                'fake-id' => "TO-{$mysql}--latest--3306",
                'fake-id' => "TO-{$postgres}--latest--5432",
            ]);
            $mock->shouldIgnoreMissing();
        });

        $this->artisan("disable {$mysql} {$postgres}");
    }

    /** @test */
    public function all_services_will_be_disabled_if_all_flag_passed()
    {
        $mysql = 'mysql';
        $postgres = 'postgres';

        $this->mock(DisableCommand::class, function ($mock) use ($mysql, $postgres) {
            $mock->shouldReceive('disableByContainerId');
            $mock->shouldReceive('disableableServices')->andReturn([
                'fake-id' => "TO-{$mysql}--latest--3306",
                'fake-id' => "TO-{$postgres}--latest--5432",
            ]);

            $mock->shouldIgnoreMissing();
        });

        $this->artisan('disable', [
            '--all' => true,
        ]);
    }
}
