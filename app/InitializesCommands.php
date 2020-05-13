<?php

namespace App;

use App\Exceptions\DockerMissingException;
use App\Shell\Shell;

trait InitializesCommands
{
    public function initializeCommand()
    {
        app()->bind('console', function () {
            return $this;
        });

        $this->validateDockerInstalled();
    }

    protected function validateDockerInstalled()
    {
        $shell = $this->shell ?? app(Shell::class);
        $process = $shell->exec('docker --version 2>&1');

        if ($process->getExitCode() !== 0) {
            throw new DockerMissingException;
        }
    }
}
