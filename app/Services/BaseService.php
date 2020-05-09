<?php

namespace App\Services;

use App\WritesToConsole;

abstract class BaseService
{
    use WritesToConsole;

    protected $install;
    protected $defaultPort;
    protected $defaultPrompts = [
        [
            'shortname' => 'port',
            'prompt' => 'Which host port would you like this service to use?',
            // Default is set in buildInstallString()
        ],
        [
            'shortname' => 'tag',
            'prompt' => 'Which tag (version) of this service would you like to use?',
            'default' => 'latest',
        ],
    ];
    protected $prompts;
    protected $promptResponses;

    public function install(): void
    {
        $this->prompts();

        // basically shell script running "docker run" and then this->install?
        // @todo Copy the Shell object from Lambo, probably, then inject it
        // in the constructor here?

        // $this->shell->exec($this->buildInstallString());
        $this->info('Purportedly installing ' . static::class);
    }

    public function prompts()
    {
        // @todo:
        // prompt user for port
        // prompt user for tags
        // prompt user for anything custom to this package

        // Store prompt responses on this class so the actual installation has access to the answers
    }

    public function buildInstallString(): string
    {
        // @todo replace all {variable} things with prompt data
        // @todo also, set the default on the port prompt to be $this->defaultPort
        return 'docker run ' . $this->install;
    }

    public function containerName(): string
    {
        // @todo handle what if they have two MySQLs running
        return 'TO-' . $this->shortName();
    }

    public function shortName(): string
    {
        return strtolower(class_basename(static::class));
    }
}
