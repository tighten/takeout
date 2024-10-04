<?php

namespace App\Commands;

use App\InitializesCommands;
use App\Services;
use Illuminate\Console\Command;

class ShellCommand extends Command
{
    use InitializesCommands;

    const MENU_TITLE = 'Get a shell inside a running Takeout container.';

    protected $signature = 'shell {service}';
    protected $description = 'Get a shell inside a running Takeout container.';

    public function handle(Services $services): int
    {
        $this->initializecommand();

        $service = $services->get($this->argument('service'));

        resolve($service)->forwardShell();

        return self::SUCCESS;
    }
}
