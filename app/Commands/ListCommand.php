<?php

namespace App\Commands;

use App\InitializesCommands;
use App\Services\Category;
use App\Shell\Docker;
use LaravelZero\Framework\Commands\Command;

use function Laravel\Prompts\info;
use function Laravel\Prompts\table;

class ListCommand extends Command
{
    use InitializesCommands;

    protected $signature = 'list {--json} {--networking}';
    protected $description = 'List all services enabled by Takeout.';

    public function handle(Docker $docker): void
    {
        $this->initializeCommand();

        $containersCollection = $docker->takeoutContainers(boolval($this->option('networking')));

        if ($this->option('json')) {
            $this->line($containersCollection->map(function ($item) {
                return array_merge($item, [
                    'category' => Category::fromContainerName($item['names']),
                ]);
            })->toJson());

            return;
        }

        if ($containersCollection->isEmpty()) {
            info('No Takeout containers are enabled.');

            return;
        }

        $containers = $containersCollection->toArray();
        $columns = array_map('App\title_from_slug', array_keys(reset($containers)));

        table($columns, $containers);
    }
}
