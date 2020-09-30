<?php

namespace App\Commands;

use App\InitializesCommands;
use App\Shell\Docker;
use LaravelZero\Framework\Commands\Command;

class DiskUsageCommand extends Command
{
    use InitializesCommands;

    protected $signature = 'disk-usage {--json}';
    protected $description = 'Show disk usage.';

    public function handle(): void
    {
        $this->initializeCommand();

        $diskUsageCollection = app(Docker::class)->diskUsage();

        if ($this->option('json')) {
            $this->line($diskUsageCollection->toJson());
            return;
        }

        if ($diskUsageCollection->isEmpty()) {
            $this->info("No Takeout containers are enabled.\n");
            return;
        }

        $containers = $diskUsageCollection->toArray();
        $columns = array_map('App\title_from_slug', array_keys(reset($containers)));

        $this->line("\n");
        $this->table($columns, $containers);
    }
}
