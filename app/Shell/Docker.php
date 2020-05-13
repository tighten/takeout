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
}
