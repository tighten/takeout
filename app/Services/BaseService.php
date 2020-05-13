<?php

namespace App\Services;

use App\Shell\Shell;
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
    protected $shell;

    public function __construct(Shell $shell)
    {
        $this->shell = $shell;
    }

    public function install(): void
    {
        $this->prompts();
        $this->info('Installing ' . $this->shortName());
        $this->info('RUN: ' . $this->buildInstallString());
        // $this->shell->exec($this->buildInstallString());
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
        $install = $this->install; // @Todo replace all variables
        // @todo replace all {variable} things with prompt data
        // @todo also, set the default on the port prompt to be $this->defaultPort
        // @todo also use containerName() to set containername
        return 'docker run ' . $install;
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
