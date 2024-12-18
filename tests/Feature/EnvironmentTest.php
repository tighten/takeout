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

        $environment = app(Environment::class);

        $port = $this->withFakeProcessRunning(
            fn($port) => $this->assertFalse($environment->portIsAvailable($port), "Expected port {$port} to be taken, but it was available."),
        );

        $this->assertTrue($environment->portIsAvailable($port), "Expected port {$port} to be avaialble, but it was taken.");
    }

    private function withFakeProcessRunning($closure)
    {
        // Passing zero to it will make PHP select a free port...
        $socket = socket_create_listen(0);

        // Extract the host and port (we only care about the port) so we can use it...
        socket_getsockname($socket, $host, $port);

        try {
            $closure($port);
        } finally {
            socket_close($socket);
        }
    }
}
