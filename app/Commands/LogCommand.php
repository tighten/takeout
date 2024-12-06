<?php

namespace App\Commands;

use App\InitializesCommands;
use App\Shell\Docker;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;

class LogCommand extends Command
{
    use InitializesCommands;

    protected $signature = 'logs {containerId?}';
    protected $description = 'Display container logs.';
    protected $docker;

    public function handle(Docker $docker): void
    {
        $this->docker = $docker;
        $this->initializeCommand();

        $container = $this->argument('containerId');

        if (! $container) {
            $this->error("Please pass a valid container ID.\n");

            return;
        }

        $this->logs($container);
    }

    public function logs(string $container): void
    {
        if (Str::contains($container, ' -')) {
            $container = Str::before($container, ' -');
        }

        $this->docker->logContainer($container);
    }
}
