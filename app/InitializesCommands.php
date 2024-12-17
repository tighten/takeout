<?php

namespace App;

use App\Exceptions\DockerMissingException;
use App\Exceptions\DockerNotAvailableException;
use App\Shell\Docker;

use function Laravel\Prompts\text;

trait InitializesCommands
{
    public function initializeCommand(): void
    {
        app()->bind('console', function () {
            return $this;
        });

        if (! app(Docker::class)->isInstalled()) {
            throw new DockerMissingException;
        }

        if (! app(Docker::class)->isDockerServiceRunning()) {
            throw new DockerNotAvailableException;
        }
    }

    public function askPromptQuestion(string $question, $default = null)
    {
        return text(label: $question, default: $default);
    }

    public function errorPrompt(string $message): void
    {
        $this->components->error($message);
    }

    public function alertPrompt(string $message): void
    {
        $this->components->alert($message);
    }

    public function warnPrompt(string $message): void
    {
        $this->components->warn($message);
    }

    public function linePrompt(string $message): void
    {
        $this->components->line($message);
    }

    public function infoPrompt(string $message): void
    {
        $this->components->info($message);
    }

    public function taskPrompt(string $message, $callable): void
    {
        $this->components->task($message, $callable);
    }
}
