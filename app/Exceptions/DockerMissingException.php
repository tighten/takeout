<?php

namespace App\Exceptions;

use Exception;

use function Laravel\Prompts\error;
use function Laravel\Prompts\note;

class DockerMissingException extends Exception
{
    public function render($request = null): void
    {
        error('Docker is not installed.');
        note('Please visit https://docs.docker.com/get-docker/' . PHP_EOL . 'for information on how to install Docker for your machine.');
    }
}
