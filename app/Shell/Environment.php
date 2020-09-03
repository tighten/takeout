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
        $process = $this->shell->execQuietly("netstat -vanp tcp | grep {$port}");

        // A successful netstat command means a port in use was found
        return ! $process->isSuccessful();
    }

    public function portInfo($port): array
    {
        $process = $this->shell
            ->execQuietly("lsof -n -P | grep -Ei 'ipv4.*:{$port}' |  tr -s ' ' | cut -d' ' -f1,2,3");
        return $process->isSuccessful() ? explode(" ", trim($process->getOutput())) : [];
    }
}
