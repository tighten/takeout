<?php


namespace App\Exceptions;


use App\Shell\Shell;

class DockerContainerMissingException extends \Exception
{
    public function render($request = null): void
    {
        $console = app('console');
        $shell = app(Shell::class);

        $console->line('');
        $console->line($shell->formatErrorMessage('Docker container not found.'));
    }
}
