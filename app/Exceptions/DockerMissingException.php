<?php

namespace App\Exceptions;

use App\Shell\Shell;
use Exception;

class DockerMissingException extends Exception
{
    public function render($request = null): void
    {
        $console = app('console');
        $shell = app(Shell::class);

        $console->line('');
        $console->line($shell->formatErrorMessage('Docker is not installed.'));
        $console->line('');
        $console->line($shell->formatErrorMessage('Please visit https://docs.docker.com/docker-for-mac/install/'));
        $console->line($shell->formatErrorMessage('for information on how to install Docker for your machine.'));
    }
}
