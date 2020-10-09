<?php


namespace App\Exceptions;
use Exception;

use App\Shell\Shell;
use Throwable;

class DockerContainerMissingException extends Exception
{
    public function render($request = null): void
    {
        $console = app('console');
        $shell = app(Shell::class);

        $console->line('');
        $console->line($shell->formatErrorMessage($this->getMessage()));
    }
}
