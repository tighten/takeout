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
        $process = $this->shell->execQuietly('docker --version 2>&1');

        return $process->getExitCode() === 0;
    }

    public function containers(): Process
    {
        return $this->shell->execQuietly('docker ps -a --filter "name=TO-" --format "table {{.ID}}\t{{.Names}}"');
    }
}
