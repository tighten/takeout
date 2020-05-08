<?php

namespace App\Packages;

use App\WritesToConsole;

abstract class BasePackage
{
    use WritesToConsole;

    protected $install;

    public function install(): void
    {
        // basically shell script running "docker run" and then this->install?
        // @todo Copy the Shell object from Lambo, probably, then inject it
        // in the constructor here?
        // $this->shell->exec($this->buildInstallString());
        $this->info('Purportedly installing ' . static::class);
    }

    public function buildInstallString(): string
    {
        return 'docker run ' . $this->install;
    }
}
