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

    public function removeContainer(string $containerId): void
    {
        $this->stopContainer($containerId);

        $process = $this->shell->exec('docker rm ' . $containerId);

        if (! $process->isSuccessful()) {
            throw new Exception('Failed removing container ' . $containerId);
        }
    }

    public function stopContainer(string $containerId): void
    {
        $process = $this->shell->exec('docker stop ' . $containerId);

        if (! $process->isSuccessful()) {
            throw new Exception('Failed stopping container ' . $containerId);
        }
    }

    public function isInstalled(): bool
    {
        $process = $this->shell->execQuietly('docker --version 2>&1');

        return $process->isSuccessful();
    }

    public function containers(): array
    {
        $output = trim($this->containersRawOutput()->getOutput());

        return array_filter(array_map(function ($line) {
            return explode(',', $line);
        }, explode("\n", $output)));
    }

    protected function containersRawOutput(): Process
    {
        return $this->shell->execQuietly('docker ps -a --filter "name=TO-" --format "table {{.ID}},{{.Names}},{{.Ports}},{{.Status}}"');
    }

    public function imageIsDownloaded(string $organization, string $imageName, ?string $tag): bool
    {
        $process = $this->shell->execQuietly(sprintf(
            'docker image inspect %s/%s:%s',
            $organization,
            $imageName,
            $tag
        ));

        return $process->isSuccessful();
    }

    public function downloadImage(string $organization, string $imageName, ?string $tag): void
    {
        $this->shell->exec(sprintf(
            'docker pull %s/%s:%s',
            $organization,
            $imageName,
            $tag
        ));
    }

    public function bootContainer(string $installTemplate, array $parameters): void
    {
        $process = $this->shell->exec('docker run -d --name "$container_name" ' . $installTemplate, $parameters);

        if (! $process->isSuccessful()) {
            throw new Exception("Failed installing {$containerName}");
        }
    }
}
