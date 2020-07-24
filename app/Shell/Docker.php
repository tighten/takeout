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

    public function isInstalled(): Bool
    {
        $process = $this->shell->execQuietly('docker --version 2>&1');

        return $process->getExitCode() === 0;
    }

    public function containersRawOutput(): Process
    {
        return $this->shell->execQuietly('docker ps -a --filter "name=TO-" --format "table {{.ID}},{{.Names}},{{.Status}}"');
    }

    public function containers(): array
    {
        $output = $this->containersRawOutput()->getOutput();
        return array_filter(array_map(function ($line) {
            return array_filter(explode(',', $line));
        }, explode("\n", $output)));
    }

    public function imageIsDownloaded($organization, $imageName, $tag): Bool
    {
        $process = $this->shell->execQuietly(sprintf(
            "docker image inspect %s/%s:%s",
            $organization,
            $imageName,
            $tag
        ));

        return $process->isSuccessful();
    }

    public function downloadImage($organization, $imageName, $tag)
    {
        $this->shell->exec(sprintf(
            "docker pull %s/%s:%s",
            $organization,
            $imageName,
            $tag
        ));
    }

    public function bootContainer($containerName, $installString)
    {
        $process = $this->shell->exec("docker run -d --name={$containerName} $installString");

        if (! $process->isSuccessful()) {
            throw new Exception("Failed installing {$containerName}");
        }
    }
}
