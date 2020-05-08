<?php

namespace App\Packages;

abstract class BasePackage
{
    protected $install;

    public function install(): void
    {
        // basically shell script running "docker run" and then this->install?
        // @todo Copy the Shell object from Lambo, probably, then inject it
        // in the constructor here?
        // $this->shell->exec($this->buildInstallString());
    }

    public function buildInstallString(): string
    {
        return 'docker run ' . $this->install;
    }
}
