<?php

namespace App\Commands;

use App\InitializesCommands;
use App\Shell\Docker;
use App\Shell\Environment;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;

use function Laravel\Prompts\select;

class StopCommand extends Command
{
    use InitializesCommands;

    const MENU_TITLE = 'Takeout containers to stop';

    protected $signature = 'stop {containerId?*} {--all}';
    protected $description = 'Stop one ore more started containers.';
    protected $docker;
    protected $environment;

    public function handle(Docker $docker, Environment $environment): void
    {

        $this->docker = $docker;
        $this->environment = $environment;
        $this->initializeCommand();
        $stoppableContainers = $this->stoppableContainers();

        if ($this->option('all')) {
            $stoppableContainers->keys()->each(function ($containerId) {
                $this->stop($containerId);
            });

            return;
        }

        if ($stoppableContainers->isEmpty()) {
            $this->info("No Takeout containers available to stop.\n");

            return;
        }

        if (filled($services = $this->argument('containerId'))) {
            foreach ($services as $service) {
                $this->stopByServiceNameOrContainerId($service, $stoppableContainers);
            }

            return;
        }

        $this->stop($this->selectOptions($stoppableContainers));
    }

    private function stoppableContainers(): Collection
    {
        return $this->docker->activeTakeoutContainers()->mapWithKeys(function ($container) {
            return [$container['container_id'] => str_replace('TO--', '', $container['names'])];
        });
    }

    private function stopByServiceNameOrContainerId(string $service, Collection $stoppableContainers): void
    {
        $containersByServiceName = $stoppableContainers
            ->filter(function ($containerName, $key) use ($service) {
                return Str::startsWith($containerName, $service) || $key === $service;
            });

        if ($containersByServiceName->isEmpty()) {
            $this->info('No containers found for ' . $service);

            return;
        }

        if ($containersByServiceName->count() === 1) {
            $this->stop($containersByServiceName->keys()->first());

            return;
        }

        $this->stop($this->selectOptions($containersByServiceName));
    }

    public function stop(string $container): void
    {
        if (Str::contains($container, ' -')) {
            $container = Str::before($container, ' -');
        }

        $this->docker->stopContainer($container);
    }

    private function selectOptions($stoppableContainers)
    {
        return select(
            label: self::MENU_TITLE,
            options: $stoppableContainers
        );
    }
}
