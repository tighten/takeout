<?php

namespace App\Services;

use App\Shell\Shell;
use App\WritesToConsole;

abstract class BaseService
{
    use WritesToConsole;

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

    public function __construct(Shell $shell)
    {
        $this->shell = $shell;
        $this->defaultPrompts = array_map(function ($prompt) {
           if ($prompt['shortname'] === 'port') {
               $prompt['default'] = $this->defaultPort;
           }
           return $prompt;
        }, $this->defaultPrompts);
    }

    public function install(): void
    {
        $this->prompts();
        $this->info('Installing ' . $this->shortName());
        //$this->info('RUN: ' . $this->buildInstallString());
        $this->shell->exec($this->buildInstallString());
    }

    public function prompts()
    {
        foreach ($this->defaultPrompts as $prompt) {
            $this->askQuestion($prompt);
        }

        foreach ($this->prompts as $prompt) {
            $this->askQuestion($prompt);
        }
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
        // @todo handle what if they have two MySQLs running
        return 'TO-' . $this->shortName();
    }

    public function shortName(): string
    {
        return strtolower(class_basename(static::class));
    }

    public function askQuestion($prompt): void
    {
        $this->promptResponses[$prompt['shortname']] = app('console')->ask($prompt['prompt'], $prompt['default'] ?? null );
    }
}
