<?php

namespace App\Commands;

use App\InitializesCommands;
use App\Shell\Docker;
use LaravelZero\Framework\Commands\Command;

class ListCommand extends Command
{
    use InitializesCommands;

    protected $signature = 'list';
    protected $description = 'List all services enabled by Takeout.';

    public function handle(): void
    {
        $this->initializeCommand();

        $containers = app(Docker::class)->containers();

        $this->line("\n");
        $this->table(array_shift($containers), $containers);
    }
}
