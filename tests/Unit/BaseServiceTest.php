<?php

namespace Tests\Unit;

use App\Services\MeiliSearch;
use App\Shell\Shell;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Process\Process;
use Tests\TestCase;
use Mockery as M;

class BaseServiceTest extends TestCase
{
    /** @test **/
    public function it_detects_a_port_conflict()
    {
        app()->instance('console', M::mock(Command::class, function ($mock) {
            $mock->shouldIgnoreMissing();
        }));

        $this->mock(Shell::class, function ($mock) {
            $process = M::mock(Process::class);
            $process->shouldReceive('isSuccessful')->once()->andReturn(true);

            $mock->shouldReceive('execQuietly')->once()->andReturn($process);
        });

        $service = app(MeiliSearch::Class);
        $service->promptResponse['port'] = 7700;

        $this->assertTrue($service->portIsUnavailable());
    }

    /** @test **/
    public function it_detects_a_port_is_available()
    {
        app()->instance('console', M::mock(Command::class, function ($mock) {
            $mock->shouldIgnoreMissing();
        }));

        $this->mock(Shell::class, function ($mock) {
            $process = M::mock(Process::class);
            $process->shouldReceive('isSuccessful')->once()->andReturn(false);

            $mock->shouldReceive('execQuietly')->once()->andReturn($process);
        });

        $service = app(MeiliSearch::Class);
        $service->promptResponse['port'] = 7700;

        $this->assertFalse($service->portIsUnavailable());
    }
}
