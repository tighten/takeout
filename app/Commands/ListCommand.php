<?php

namespace App\Commands;

use App\InitializesCommands;
use App\Shell\Docker;
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

        $containers = app(Docker::class)->takeoutContainers();

        if ($this->option('json')) {
            $keys = collect(array_shift($containers))->map(function ($key) {
                return Str::slug($key, '_');
            })->toArray();

            $containers = collect($containers)->map(function ($container) use ($keys) {
                return array_combine($keys, $container);
            });

            $this->line(json_encode($containers));

            return;
        }

        $this->line("\n");
        $this->table(array_shift($containers), $containers);
    }
}
