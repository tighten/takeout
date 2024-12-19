<?php

namespace App\Exceptions;

use App\Shell\Environment;
use Exception;

use function Laravel\Prompts\error;
use function Laravel\Prompts\note;

class DockerNotAvailableException extends Exception
{
    public function render($request = null): void
    {
        error('Docker is not available.');

        if (PHP_OS_FAMILY === 'Darwin') {
            note(implode(PHP_EOL, [
                'Open Docker for Mac or start it from the terminal running:',
                '',
                '  open --background -a Docker',
            ]));
        } elseif (PHP_OS_FAMILY === 'Windows') {
            note('Open Docker for Windows to start the Docker service.');
        } else {
            note(implode(PHP_EOL, [
                'Verify that the Docker service is running:',
                '',
                '  sudo systemctl status docker',
                '',
                'Start the Docker service:',
                '',
                '  sudo systemctl start docker',
            ]));

            $environment = app(Environment::class);

            if (! $environment->userIsInDockerGroup()) {
                error('You are not in the docker group.');
                note(implode(PHP_EOL, [
                    'You need to be in that group to run Docker as non-root. Add it by running:',
                    '',
                    '  sudo usermod -aG docker ${USER}',
                    '',
                    'and restart your session.',
                ]));
            }
        }
    }
}
