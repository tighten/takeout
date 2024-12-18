<?php

namespace Tests\Feature;

use App\Shell\Environment;
use Tests\TestCase;

class EnvironmentTest extends TestCase
{
    /** @test **/
    public function it_detects_a_port_conflict()
    {
        if (! extension_loaded('sockets')) {
            $this->markTestSkipped('Sockets extension is required (should be included in PHP by default).');
        }

        $port = rand(20_000, 50_000);

        $environment = app(Environment::class);

        $this->assertTrue($environment->portIsAvailable($port));

        $this->bindFakeProcessToPort($port, fn() => (
            $this->assertFalse($environment->portIsAvailable($port), "Expected port {$port} to be in use, but it was available.")
        ));
    }

    private function bindFakeProcessToPort(int $port, $callback)
    {
        $socket = socket_create(domain: AF_INET, type: SOCK_STREAM, protocol: SOL_TCP);

        assert($socket !== false, 'Was not able to create a socket.');
        assert(socket_bind($socket, '127.0.0.1', $port) !== false, "Was not able to bind socket to port {$port}");
        assert(socket_listen($socket));

        try {
            $callback();
        } finally {
            socket_close($socket);
        }
    }
}
