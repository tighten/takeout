<?php

namespace App\Commands;

use App\InitializesCommands;
use App\Shell\Docker;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;

class ListCommand extends Command
{
    use InitializesCommands;

    protected $signature = 'list {--json}';
    protected $description = 'List all services enabled by Takeout.';

    public function handle(): void
    {
        $this->initializeCommand();

        if ($this->option('json')) {
            $this->line(app(Docker::class)->takeoutContainers()->toJson());
            return;
        }

        $containers = app(Docker::class)->takeoutContainers()->toArray();
        $columns = array_map('App\title_from_slug', array_keys(reset($containers)));

        $this->line("\n");
        $this->table($columns, $containers);
    }
}
