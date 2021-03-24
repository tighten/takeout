<?php

namespace App\Exceptions;

use App\Shell\Environment;
use App\Shell\Shell;
use Exception;

class DockerNotAvailableException extends Exception
{
    public function render($request = null): void
    {
        $console = app('console');
        $shell = app(Shell::class);
        $environment = app(Environment::class);

        $console->line('');
        $console->line($shell->formatErrorMessage('Docker is not available.'));
        $console->line('');

        if ($environment->isLinuxOs()) {
            $console->line('Verify that the Docker service is running:');
            $console->line('  sudo systemctl status docker');
            $console->line('Start the Docker service:');
            $console->line('  sudo systemctl start docker');

            if (! $environment->userIsInDockerGroup()) {
                $console->line('');
                $console->line($shell->formatErrorMessage('You are not in the docker group.'));
                $console->line('');
                $console->line('This is required to run Docker as a non-root user.');
                $console->line('Add your user to the docker group by running:');
                $console->line('  sudo usermod -aG docker ${USER}');
                $console->line('and restart your session.');
            }
        } elseif ($environment->isWindowsOs()) {
            $console->line('Open Docker for Windows');
            $console->line('to start the Docker service.');
        } else {
            $console->line('Open Docker for Mac or run:');
            $console->line('  open --background -a Docker');
            $console->line('to start the Docker service.');
        }
    }
}
