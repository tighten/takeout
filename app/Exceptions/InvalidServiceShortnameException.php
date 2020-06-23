<?php

namespace App\Exceptions;

use App\Shell\Shell;
use Exception;

class InvalidServiceShortnameException extends Exception
{
    public function render($request = null)
    {
        $console = app('console');
        $shell = app(Shell::class);

        $console->line('');
        $console->line($shell->formatErrorMessage($this->getMessage()));
    }
}
