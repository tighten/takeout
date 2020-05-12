<?php

namespace App;

trait InitializesCommands
{
    public function initializeCommand()
    {
        app()->bind('console', function () {
            return $this;
        });

        $this->validateDockerInstalled();
    }

    protected function validateDockerInstalled()
    {
        exec('docker --version 2>&1', $output, $exitCode);

        if ($exitCode !== 0) {
            $this->error('Docker is not installed. Please visit https://docs.docker.com/docker-for-mac/install/ for information on how to install Docker for your machine.');
            exit;
        }
    }
}
