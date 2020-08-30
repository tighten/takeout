<?php

namespace App\Shell;

class Environment
{
    protected $shell;

    public function __construct(Shell $shell)
    {
        $this->shell = $shell;
    }

    public function portIsAvailable($port): bool
    {
        // Check to see if the system is running a service with the desired port
        $process = $this->shell->execQuietly("lsof -i :{$port} | grep 'com.dock'");

        // A successful netstat command means a port in use was found
        return ! $process->isSuccessful();
    }
}
