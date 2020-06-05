<?php

namespace App\Shell;

use Symfony\Component\Process\Process;

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

    public function containers(): Process
    {
        // @todo format or group or whatever
        return $this->shell->exec('docker ps -a --filter "name=TO-" --format "table {{.ID}}\t{{.Names}}"');
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
