<?php

namespace App\Commands;

use App\InitializesCommands;
use App\Shell\Docker;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;

use function Laravel\Prompts\select;

class LogCommand extends Command
{
    use InitializesCommands;

    const MENU_TITLE = 'Takeout containers logs';

    protected $signature = 'logs {containerId?}';
    protected $description = 'Display container logs.';
    protected $docker;

    public function handle(Docker $docker): void
    {
        $this->docker = $docker;
        $this->initializeCommand();

        $loggableContainers = $this->loggableContainers();

        if ($loggableContainers->isEmpty()) {
            $this->info("No Takeout containers available.\n");

            return;
        }

        if (filled($service = $this->argument('containerId'))) {
            $this->logsByServiceNameOrContainerId($service, $loggableContainers);

            return;
        }

        $this->logs($this->selectOptions($loggableContainers));
    }


    public function logs(string $container): void
    {
        if (Str::contains($container, ' -')) {
            $container = Str::before($container, ' -');
        }

        $this->docker->logContainer($container);
    }

    private function loggableContainers(): Collection
    {
        return $this->docker->activeTakeoutContainers()->mapWithKeys(function ($container) {
            return [$container['container_id'] => str_replace('TO--', '', $container['names'])];
        });
    }

    private function selectOptions($stoppableContainers)
    {
        return select(
            label: self::MENU_TITLE,
            options: $stoppableContainers
        );
    }

    private function logsByServiceNameOrContainerId(string $service, Collection $loggableContainers): void
    {
        $containersByServiceName = $loggableContainers
            ->filter(function ($containerName, $key) use ($service) {
                return Str::startsWith($containerName, $service) || $key === $service;
            });

        if ($containersByServiceName->isEmpty()) {
            $this->info('No containers found for ' . $service);

            return;
        }

        if ($containersByServiceName->count() === 1) {
            $this->logs($containersByServiceName->keys()->first());

            return;
        }

        $this->logs($this->selectOptions($containersByServiceName));
    }
}
