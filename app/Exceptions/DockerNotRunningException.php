<?php

namespace App\Exceptions;

use App\Shell\Shell;
use Exception;

class DockerNotRunningException extends Exception
{
    public function render($request = null): void
    {
        $console = app('console');
        $shell = app(Shell::class);

        $console->line('');
        $console->line($shell->formatErrorMessage('Docker is not running.'));
        $console->line('');
        $console->line('Open Docker for Mac or run:');
        $console->line('open --background -a Docker');
        $console->line('to start the Docker service.');
    }
}
