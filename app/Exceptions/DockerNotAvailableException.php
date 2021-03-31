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

        $console->line('');
        $console->line($shell->formatErrorMessage('Docker is not available.'));
        $console->line('');

        if (in_array(PHP_OS_FAMILY, ['Darwin', 'Linux', 'Windows'])) {
            $osSpecificHelp = 'helpFor' . ucfirst(PHP_OS_FAMILY);
            $this->$osSpecificHelp($console);
        }
    }

    protected function helpForDarwin($console)
    {
        $console->line('Open Docker for Mac or run:');
        $console->line('  open --background -a Docker');
        $console->line('to start the Docker service.');
    }

    protected function helpForLinux($console)
    {
        $environment = app(Environment::class);
        $shell = app(Shell::class);

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
    }

    protected function helpForWindows($console)
    {
        $console->line('Open Docker for Windows to start the Docker service.');
    }
}
