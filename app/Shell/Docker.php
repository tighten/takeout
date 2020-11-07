<?php

namespace App\Shell;

use Exception;
use Illuminate\Support\Collection;

class Docker
{
    protected $shell;
    protected $formatter;
    protected $networking;

    public function __construct(Shell $shell, DockerFormatter $formatter, DockerNetworking $networking)
    {
        $this->shell = $shell;
        $this->formatter = $formatter;
        $this->networking = $networking;
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

    public function startContainer(string $containerId): void
    {
        $process = $this->shell->exec('docker start ' . $containerId);

        if (! $process->isSuccessful()) {
            throw new Exception('Failed starting container ' . $containerId);
        }
    }

    public function isInstalled(): bool
    {
        return $this->shell->execQuietly('docker --version 2>&1')->isSuccessful();
    }

    public function takeoutContainers(): Collection
    {
        $process = sprintf(
            "docker ps -a --filter 'name=TO-' --format 'table %s|%s'",
            '{{.ID}}|{{.Names}}|{{.Status}}|{{.Ports}}',
            '{{.Label "com.tighten.takeout.Base_Alias"}}|{{.Label "com.tighten.takeout.Full_Alias"}}'
        );

        return $this->formatter->rawTableOutputToCollection(
            $this->shell->execQuietly($process)->getOutput()
        );
    }

    public function allContainers(): Collection
    {
        $process = 'docker ps --format "table {{.ID}}|{{.Names}}|{{.Status}}|{{.Ports}}"';
        $output = $this->shell->execQuietly($process)->getOutput();
        return $this->formatter->rawTableOutputToCollection($output);
    }

    public function volumeIsAvailable(string $volumeName): bool
    {
        return $this->listMatchingVolumes($volumeName)->isEmpty();
    }

    public function listMatchingVolumes(string $volumeName): Collection
    {
        $process = "docker ps -a --filter volume={$volumeName} --format 'table {{.ID}}|{{.Names}}|{{.Status}}|{{.Ports}}'";
        $output = $this->shell->execQuietly($process)->getOutput();
        return $this->formatter->rawTableOutputToCollection($output);
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

    public function bootContainer(string $dockerRunTemplate, array $parameters): void
    {
        $this->networking->ensureNetworkCreated();

        $command = sprintf(
            'docker run -d --name "${:container_name}" %s %s',
             $this->networking->networkSettings($parameters['alias'], $parameters['image_name']),
             $dockerRunTemplate
        );

        $process = $this->shell->exec($command, $parameters);

        if (! $process->isSuccessful()) {
            throw new Exception("Failed installing " . $parameters['image_name']);
        }
    }

    public function attachedVolumeName(string $containerId)
    {
        $response = $this->shell->execQuietly("docker inspect --format='{{json .Mounts}}' {$containerId}");
        $jsonResponse = json_decode($response->getOutput());
        return optional($jsonResponse)[0]->Name ?? null;
    }

    public function isDockerServiceRunning(): bool
    {
        return $this->shell->execQuietly('docker info')->isSuccessful();
    }

    public function stopDockerService(): void
    {
        $this->shell->execQuietly("test -z $(docker ps -q 2>/dev/null) && osascript -e 'quit app \"Docker\"'");
    }
}
