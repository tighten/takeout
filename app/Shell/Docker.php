<?php

namespace App\Shell;

use Exception;
use Symfony\Component\Process\Process;

class Docker
{
    protected $shell;

    public function __construct(Shell $shell)
    {
        $this->shell = $shell;
    }

    public function removeContainer(string $containerId)
    {
        $this->stopContainer($containerId);

        $output = $this->shell->exec('docker rm ' . $containerId);

        if ($output->getExitCode() !== 0) {
            throw new Exception('Failed removing container ' . $containerId);
        }
    }

    public function stopContainer(string $containerId)
    {
        $output = $this->shell->exec('docker stop ' . $containerId);

        if ($output->getExitCode() !== 0) {
            throw new Exception('Failed stopping container ' . $containerId);
        }
    }

    public function isInstalled()
    {
        $process = $this->shell->execQuietly('docker --version 2>&1');

        return $process->getExitCode() === 0;
    }

    public function containersRawOutput(): Process
    {
        return $this->shell->execQuietly('docker ps -a --filter "name=TO-" --format "table {{.ID}}\t{{.Names}}"');
    }

    public function containers(): array
    {
        $output = $this->containersRawOutput()->getOutput();

        return array_filter(array_map(function ($line) {
           return array_filter(explode("        ", $line));
        }, explode("\n", $output)));
    }
}
