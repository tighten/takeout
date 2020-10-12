<?php

namespace Tests\Feature;

use Mockery as M;
use Tests\TestCase;
use App\Shell\Docker;
use App\Services\MailHog;

class ServiceEnableDefaultTest extends TestCase
{
    /** @test */
    public function it_enables_service_when_default_flag_is_provided()
    {
        app()->instance('console', M::mock(Command::class, function ($mock) {
            $defaultPort = app(MailHog::class)->defaultPort();
            $mock->shouldReceive('ask')->with('Which host port would you like this service to use?', $defaultPort)->andReturn(7700);
            $mock->shouldReceive('ask')->with('Which host port would you like this service to use?', $defaultPort)->andReturn(77001);
            $mock->shouldReceive('ask')->with('Which tag (version) of this service would you like to use?', 'latest')->andReturn('latest');
            $mock->shouldIgnoreMissing();
        }));

        $this->mock(Shell::class, function ($mock) {
            $process = M::mock(Process::class);
            $process->shouldReceive('isSuccessful')->andReturn(true);
            $process->shouldReceive('isSuccessful')->andReturn(false);
            $process->shouldReceive('getOutput')->andReturn('');

            $mock->shouldReceive('execQuietly')->andReturn($process);
        });

        $service = 'mailhog';
        $this->mock(MailHog::class, function ($mock) use ($service) {
            $mock->shouldReceive('enable')->with(true)->twice();
            $mock->shouldReceive('shortName')->andReturn($service);
        });

        $this->mock(Docker::class, function ($mock) {
            $mock->shouldReceive('isInstalled')->andReturn(true);
            $mock->shouldReceive('isDockerServiceRunning')->andReturn(true);
            $mock->shouldReceive('imageIsDownloaded')->andReturn(true);

            // This is the actual assertion
            $mock->shouldReceive('bootContainer')->with(['mailhog/mailhog']);
        });

        $this->artisan('enable ' . $service . ' --default');

        $service = app(MailHog::class); // Extends BaseService
        $service->enable(true);
    }
}
