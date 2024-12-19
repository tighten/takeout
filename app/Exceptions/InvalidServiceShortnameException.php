<?php

namespace App\Exceptions;

use Exception;

use function Laravel\Prompts\error;

class InvalidServiceShortnameException extends Exception
{
    public function render($request = null)
    {
        error($this->getMessage());
    }
}
