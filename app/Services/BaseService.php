<?php

namespace App\Services;

use App\Shell\Docker;
use App\Shell\DockerTags;
use App\Shell\Environment;
use App\Shell\Shell;
use App\WritesToConsole;
use Throwable;

abstract class BaseService
{
    use WritesToConsole;

    protected $organization = 'library'; // Official repositories use `library` as the organization name.
    protected $imageName;
    protected $tag;
    protected $installTemplate;
    protected $defaultPort;
    protected $defaultPrompts = [
        [
            'shortname' => 'PORT',
            'prompt' => 'Which host port would you like this service to use?',
            // Default is set in the constructor
        ],
        [
            'shortname' => 'TAG',
            'prompt' => 'Which tag (version) of this service would you like to use?',
            'default' => 'latest',
        ],
    ];
    protected $prompts;
    protected $promptResponses = [];
    protected $shell;
    protected $environment;
    protected $docker;

    public function __construct(Shell $shell, Environment $environment, Docker $docker, DockerTags $dockerTags)
    {
        $this->shell = $shell;
        $this->environment = $environment;
        $this->docker = $docker;
        $this->dockerTags = $dockerTags;

        $this->defaultPrompts = array_map(function ($prompt) {
            if ($prompt['shortname'] === 'PORT') {
                $prompt['default'] = $this->defaultPort;
            }
            return $prompt;
        }, $this->defaultPrompts);

        $this->promptResponses = [
            'ORGANIZATION' => $this->organization,
            'IMAGE_NAME' => $this->imageName,
        ];
    }

    public function install()
    {
        $this->prompts();
        $this->ensureImageIsDownloaded();

        $this->info("Installing {$this->shortName()}...\n");

        try {
            $this->docker->bootContainer(
                $this->installTemplate,
                $this->buildParameters(),
            );

            $this->info("\nInstallation complete!");
        } catch (Throwable $e) {
            return $this->error("\nInstallation failed!");
        }
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

    protected function ensureImageIsDownloaded()
    {
        if ($this->docker->imageIsDownloaded($this->organization, $this->imageName, $this->tag)) {
            return;
        }

        $this->info("Downloading docker image...\n");
        $this->docker->downloadImage($this->organization, $this->imageName, $this->tag);
    }

    protected function prompts()
    {
        foreach ($this->defaultPrompts as $prompt) {
            $this->askQuestion($prompt);

            while ($prompt['shortname'] === 'PORT' && ! $this->environment->portIsAvailable($this->promptResponses['PORT'])) {
                app('console')->error("Port {$this->promptResponses['port']} is already in use. Please select a different port.\n");
                $this->askQuestion($prompt);
            }
        }

        foreach ($this->prompts as $prompt) {
            $this->askQuestion($prompt);
        }

        $this->tag = $this->resolveTag($this->promptResponses['TAG']);
    }

    protected function askQuestion($prompt): void
    {
        $this->promptResponses[$prompt['shortname']] = app('console')->ask($prompt['prompt'], $prompt['default'] ?? null);
    }

    protected function resolveTag($responseTag)
    {
        if ($responseTag === 'latest') {
            return $this->dockerTags->getLatestTag($this->organization, $this->imageName);
        }

        return $responseTag;
    }

    protected function buildParameters()
    {
        $parameters = $this->promptResponses;
        $parameters['CONTAINER_NAME'] = $this->containerName();
        return $parameters;
    }

    protected function containerName(): string
    {
        return 'TO--' . $this->shortName() . '--' . $this->tag;
    }
}
