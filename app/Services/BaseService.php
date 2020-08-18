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
    protected $dockerTagsClass = DockerTags::class;
    protected $tag;
    protected $installTemplate;
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
    protected $promptResponses = [];
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

        $this->promptResponses = [
            'organization' => $this->organization,
            'image_name' => $this->imageName,
        ];
    }

    public function install(): void
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
            $this->error("\nInstallation failed!");
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

    public function defaultPort(): int
    {
        return $this->defaultPort;
    }

    protected function ensureImageIsDownloaded(): void
    {
        if ($this->docker->imageIsDownloaded($this->organization, $this->imageName, $this->tag)) {
            return;
        }

        $this->info("Downloading docker image...\n");
        $this->docker->downloadImage($this->organization, $this->imageName, $this->tag);
    }

    protected function prompts(): void
    {
        foreach ($this->defaultPrompts as $prompt) {
            $this->askQuestion($prompt);

            while ($prompt['shortname'] === 'port' && ! $this->environment->portIsAvailable($this->promptResponses['port'])) {
                app('console')->error("Port {$this->promptResponses['port']} is already in use. Please select a different port.\n");
                $this->askQuestion($prompt);
            }
        }

        foreach ($this->prompts as $prompt) {
            $this->askQuestion($prompt);
        }

        $this->tag = $this->resolveTag($this->promptResponses['tag']);
    }

    protected function askQuestion(array $prompt): void
    {
        $this->promptResponses[$prompt['shortname']] = app('console')->ask($prompt['prompt'], $prompt['default'] ?? null);
    }

    protected function resolveTag($responseTag): string
    {
        if ($responseTag === 'latest') {
            return app()->make($this->dockerTagsClass, ['service' => $this])->getLatestTag();
        }

        return $responseTag;
    }

    protected function buildParameters(): array
    {
        $parameters = $this->promptResponses;
        $parameters['container_name'] = $this->containerName();
        $parameters['tag'] = $this->tag; // Overwrite "latest" with actual latest tag

        return $parameters;
    }

    protected function containerName(): string
    {
        return 'TO--' . $this->shortName() . '--' . $this->tag;
    }
}
