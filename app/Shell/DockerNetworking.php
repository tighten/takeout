<?php

namespace App\Shell;

class DockerNetworking
{
    protected $shell;
    protected $formatter;

    public function __construct(Shell $shell, DockerFormatter $formatter)
    {
        $this->shell = $shell;
        $this->formatter = $formatter;
    }

    public function networkSettings(string $alias, string $image_name): string
    {
        $networkSettings = [
            '--network=takeout',
            '--network-alias="${:alias}"',
            '--label com.tighten.takeout.Full_Alias=' . $alias,
        ];

        if (! $this->baseAliasExists($image_name)) {
            $networkSettings[] = '--network-alias="' . $image_name . '"';
            $networkSettings[] = '--label=com.tighten.takeout.Base_Alias=' . $image_name;
        }

        return implode(' ', $networkSettings);
    }

    public function ensureNetworkCreated($name = 'takeout'): void
    {
        // @todo test this
        if ($this->formatter->rawTableOutputToCollection($this->listMatchingNetworksRawOutput())->isEmpty()) {
            $this->shell->execQuietly('docker network create -d bridge ' . $name);
        }
    }

    public function baseAliasExists(string $name): bool
    {
        // @todo: This was never hit in our tests
        $output = $this->shell->execQuietly('docker ps --filter "label=com.tighten.takeout.Base_Alias=' . $name . '" --format "table {{.ID}}|{{.Names}}"')->getOutput();
        $collection = $this->formatter->rawTableOutputToCollection($output);

        return $collection->isNotEmpty();
    }

    protected function listMatchingNetworksRawOutput(string $networkName = 'takeout'): string
    {
        $command = "docker network ls --filter name={$networkName} --format 'table {{.ID}}|{{.Name}}'";
        return $this->shell->execQuietly($command)->getOutput();
    }
}
