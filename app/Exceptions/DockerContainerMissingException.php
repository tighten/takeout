<?php

namespace App\Exceptions;

use App\Shell\Shell;
use Exception;

class DockerContainerMissingException extends Exception
{
    public function __construct(string $containerId)
    {
        parent::__construct("Docker container {$containerId} not found.");
    }

    public function render($request = null): void
    {
        $console = app('console');
        $shell = app(Shell::class);

        $console->line('');
        $console->line($shell->formatErrorMessage($this->getMessage()));
    }
}
