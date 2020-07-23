<?php

namespace App\Services;

use App\Shell\Shell;
use App\WritesToConsole;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Stream;

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
    protected $guzzle;

    public function __construct(Shell $shell, Client $guzzle)
    {
        $this->shell = $shell;
        $this->guzzle = $guzzle;
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

        if (! $this->imageIsDownloaded()) {
            $this->info("Downloading docker image...\n");
            $this->shell->exec("docker pull {$this->buildOrgImageTagString()}");
        }

        $this->info('Installing ' . $this->shortName() . "...\n");

        $output = $this->shell->exec($this->buildInstallString());

        if ($output->getExitCode() === 0) {
            return $this->info("\nInstallation complete!");
        }

        $this->line("\n");
        $this->error('Installation failed!');
    }

    public function prompts()
    {
        foreach ($this->defaultPrompts as $prompt) {
            do {
                $this->askQuestion($prompt);
            } while ($prompt['shortname'] === 'port' && $this->portIsUnavailable());
        }

        foreach ($this->prompts as $prompt) {
            $this->askQuestion($prompt);
        }

        $this->promptResponses['organization'] = $this->organization;
        $this->promptResponses['imageName'] = $this->imageName;
        $this->tag = $this->promptResponses['tag'];
    }

    public function imageIsDownloaded()
    {
        $output = $this->shell->execQuietly("docker image inspect {$this->buildOrgImageTagString()}");
        return $output->getExitCode() === 0;
    }

    public function buildOrgImageTagString()
    {
        return "{$this->organization}/{$this->imageName}:{$this->tag}";
    }

    public function buildInstallString(): string
    {
        $placeholders = array_map(function ($key) {
            return "{{$key}}";
        }, array_keys($this->promptResponses));

        $install = str_replace($placeholders, array_values($this->promptResponses), $this->install);

        return 'docker run -d --name="' . $this->containerName() . '" ' . $install;
    }

    public function containerName(): string
    {
        if ($this->tag === 'latest') {
            $this->tag = $this->getLatestTag();
        }

        return 'TO--' . $this->shortName() . '--' . $this->tag;
    }

    public function shortName(): string
    {
        return strtolower(class_basename(static::class));
    }

    public function askQuestion($prompt): void
    {
        $this->promptResponses[$prompt['shortname']] = app('console')->ask($prompt['prompt'], $prompt['default'] ?? null);
    }

    public function getLatestTag(): string
    {
        return collect($this->getTags())->first(function ($tag) {
            return $tag !== 'latest';
        });
    }

    public function getTags(): array
    {
        return $this->filterResponseForTags($this->getTagsResponse());
    }

    public function filterResponseForTags(Stream $stream): array
    {
        return collect(json_decode($stream->getContents(), true)['results'])->map(function ($result) {
            return $result['name'];
        })->filter()->toArray();
    }

    public function getTagsResponse(): Stream
    {
        return $this->guzzle->get($this->buildTagsUrl())->getBody();
    }

    public function buildTagsUrl(): string
    {
        return "https://registry.hub.docker.com/v2/repositories/{$this->organization}/{$this->imageName}/tags";
    }

    protected function portIsUnavailable()
    {
        // Check to see if the system is running a service with the desired port
        $process = $this->shell->execQuietly("netstat -vanp tcp | grep {$this->promptResponses['port']}");

        // A successful netstat command means a port in use was found
        if ($process->isSuccessful()) {
            app('console')->error("Port {$this->promptResponses['port']} is already in use. Please select a different port.\n");
            return true;
        }

        return false;
    }
}
