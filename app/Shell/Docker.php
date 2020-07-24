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

        $process = $this->shell->exec('docker rm ' . $containerId);

        if (! $process->isSuccessful()) {
            throw new Exception('Failed removing container ' . $containerId);
        }
    }

    public function stopContainer(string $containerId)
    {
        $output = $this->shell->exec('docker stop ' . $containerId);

        if (! $process->isSuccessful()) {
            throw new Exception('Failed stopping container ' . $containerId);
        }
    }

    public function isInstalled(): Bool
    {
        $process = $this->shell->execQuietly('docker --version 2>&1');

        return $process->isSuccessful();
    }

    public function containersRawOutput(): Process
    {
        return $this->shell->execQuietly('docker ps -a --filter "name=TO-" --format "table {{.ID}}\t{{.Names}}"');
    }

    public function containers(): array
    {
        $output = $this->containersRawOutput()->getOutput();

        return array_filter(array_map(function ($line) {
            return array_filter(explode('        ', $line));
        }, explode("\n", $output)));
    }

    public function imageIsDownloaded($organization, $imageName, $tag): Bool
    {
        $process = $this->shell->execQuietly(sprintf(
            'docker image inspect %s/%s:%s',
            $organization,
            $imageName,
            $tag
        ));

        return $process->isSuccessful();
    }

    public function downloadImage($organization, $imageName, $tag)
    {
        $this->shell->exec(sprintf(
            'docker pull %s/%s:%s',
            $organization,
            $imageName,
            $tag
        ));
    }

    public function bootContainer($installTemplate, $parameters)
    {
        $process = $this->shell->exec('docker run -d --name "$CONTAINER_NAME" ' . $installTemplate, null, false, $parameters);

        if (! $process->isSuccessful()) {
            throw new Exception("Failed installing {$containerName}");
        }
    }
}
