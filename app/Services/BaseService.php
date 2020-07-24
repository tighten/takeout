<?php

namespace App\Services;

use App\Shell\Docker;
use App\Shell\Environment;
use App\Shell\Shell;
use App\WritesToConsole;

abstract class BaseService
{
    use WritesToConsole;

    protected $organization = 'library'; // Official repositories use `library` as the organization name.
    protected $imageName;
    protected $tag;
    protected $install;
    protected $defaultPort;
    protected $defaultPrompts = [
        [
            'shortname' => 'port',
            'prompt' => 'Which host port would you like this service to use?',
            // Default is set in the constructor
        ],
        [
            'shortname' => 'tag',
            'prompt' => 'Which tag (version) of this service would you like to use?',
            'default' => 'latest',
        ],
    ];
    protected $prompts;
    protected $promptResponses;
    protected $shell;
    protected $environment;
    protected $docker;

    public function __construct(Shell $shell, Environment $environment, Docker $docker)
    {
        $this->shell = $shell;
        $this->environment = $environment;
        $this->docker = $docker;

        $this->defaultPrompts = array_map(function ($prompt) {
            if ($prompt['shortname'] === 'port') {
                $prompt['default'] = $this->defaultPort;
            }
            return $prompt;
        }, $this->defaultPrompts);
    }

    public function install()
    {
        $this->prompts();

        if (! $this->docker->imageIsDownloaded($this->organization, $this->imageName, $this->tag)) {
            $this->info("Downloading docker image...\n");
            $this->docker->downloadImage($this->organization, $this->imageName, $this->tag);
        }

        $this->info("Installing {$this->shortName()}...\n");

        $output = $this->shell->exec($this->buildInstallString());

        if ($output->getExitCode() === 0) {
            return $this->info("\nInstallation complete!");
        }

        $this->line("\n");
        $this->error('Installation failed!');
    }

    protected function prompts()
    {
        foreach ($this->defaultPrompts as $prompt) {
            $this->askQuestion($prompt);

            while ($prompt['shortname'] === 'port' && ! $this->environment->portIsAvailable($this->promptResponses['port'])) {
                app('console')->error("Port {$this->promptResponses['port']} is already in use. Please select a different port.\n");
                $this->askQuestion('prompt');
            }
        }

        foreach ($this->prompts as $prompt) {
            $this->askQuestion($prompt);
        }

        $this->promptResponses['organization'] = $this->organization;
        $this->promptResponses['imageName'] = $this->imageName;
        $this->tag = $this->promptResponses['tag'];
    }

    protected function buildInstallString(): string
    {
        $placeholders = array_map(function ($key) {
            return "{{$key}}";
        }, array_keys($this->promptResponses));

        $install = str_replace($placeholders, array_values($this->promptResponses), $this->install);

        return 'docker run -d --name="' . $this->containerName() . '" ' . $install;
    }

    protected function containerName(): string
    {
        if ($this->tag === 'latest') {
            $this->tag = $this->dockerTags->getLatestTag($this->organization, $this->imageName);
        }

        return 'TO--' . $this->shortName() . '--' . $this->tag;
    }

    public function organization(): string
    {
        return $this->organization;
    }

    public function imageName(): string
    {
        return $this->imageName;
    }

    public function shortName(): string
    {
        return strtolower(class_basename(static::class));
    }

    protected function askQuestion($prompt): void
    {
        $this->promptResponses[$prompt['shortname']] = app('console')->ask($prompt['prompt'], $prompt['default'] ?? null);
    }
}
