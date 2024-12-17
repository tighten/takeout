<?php

namespace App\Exceptions;

use Exception;

use function Laravel\Prompts\error;

class DockerContainerMissingException extends Exception
{
    public function __construct(string $containerId)
    {
        parent::__construct("Docker container {$containerId} not found.");
    }

    public function render($request = null): void
    {
        error($this->getMessage());
    }
}
