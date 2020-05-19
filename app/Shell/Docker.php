<?php

namespace App\Shell;

class Docker
{
    protected $shell;

    public function __construct(Shell $shell)
    {
        $this->shell = $shell;
    }

    public function isInstalled()
    {
        $process = $this->shell->exec('docker --version 2>&1');

        return $process->getExitCode() === 0;
    }

    public function containers()
    {
        // @todo format or group or whatever
        return $this->shell->exec('docker ps -a --format "{{.Names}}" | grep "TO-*"');
    }

    public function runningContainers()
    {
        // @todo may not need?
    }

    public function stoppedContainers()
    {
        // @todo may not need?
    }
}
