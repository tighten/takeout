<?php

namespace Tests\Feature;

use App\Shell\Environment;
use LaravelZero\Framework\Commands\Command;
use Mockery as M;
use Tests\TestCase;

class EnvironmentTest extends TestCase
{
    /** @test **/
    public function it_detects_a_port_conflict()
    {
        app()->instance('console', M::mock(Command::class, function ($mock) {
            $mock->shouldIgnoreMissing();
        }));

        $port = rand(20_000, 50_000);

        $environment = app(Environment::class);
        $this->assertTrue($environment->portIsAvailable($port));

        $this->withFakeProcess($port, fn() => (
            $this->assertFalse($environment->portIsAvailable($port))
        ));
    }

    private function withFakeProcess(int $port, $callback)
    {
        $socket = socket_create(domain: AF_INET, type: SOCK_STREAM, protocol: SOL_TCP);
        assert($socket !== false, 'Was not able to create a socket.');
        socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);
        assert(socket_bind($socket, 'localhost', $port) !== false, "Was not able to bind socket to port {$port}");
        assert(socket_listen($socket, backlog: 5));

        try {
            $callback();
        } finally {
            socket_close($socket);
        }
    }
}
