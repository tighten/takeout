<?php

namespace App\Commands;

use App\InitializesCommands;
use App\Shell\Docker;
use App\Shell\Environment;
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

        $containers = $this->argument('containerId');

        if (filled($containers)) {

            foreach ($containers as $container) {
                $this->stopByServiceNameOrContainerId($container);
            }

            return;
        }

        if ($this->option('all')) {
            foreach ($this->docker->stoppableTakeoutContainers() as $stoppableContainer) {
                $this->stop($stoppableContainer['container_id']);
            }

            return;
        }

        if (! $stoppableContainers = $this->stoppableContainers()) {
            $this->info("No Takeout containers available to stop.\n");

            return;
        }

        $containerId = $this->loadMenu($stoppableContainers);

        $this->stop($containerId);
    }

    public function stoppableContainers(): array
    {
        return $this->docker->stoppableTakeoutContainers()->mapWithKeys(function ($container) {
            return [
                $container['container_id'] => $container['names'],
            ];
        }, collect())->toArray();
    }

    public function stopByServiceNameOrContainerId(string $serviceNameOrContainerId): void
    {
        $containersByServiceName = $this->docker->stoppableTakeoutContainers()
            ->map(function ($container) {
                return [
                    'container' => $container,
                    'label' => str_replace('TO--', '', $container['names']),
                ];
            })
            ->filter(function ($item) use ($serviceNameOrContainerId) {
                return Str::startsWith($item['label'], $serviceNameOrContainerId);
            });

        if ($containersByServiceName->isEmpty()) {
            $this->info('No containers found for ' . $serviceNameOrContainerId);

            return;
        }

        if ($containersByServiceName->count() === 1) {
            $this->stop($containersByServiceName->first()['container']['container_id']);
            return;
        }

        $containerId = $this->loadMenu($containersByServiceName->mapWithKeys(function ($item) {
            return [
                $item['container']['container_id'] => $item['label']
            ];
        })->all());

        $this->stop($containerId);
    }

    public function stop(string $container): void
    {
        if (Str::contains($container, ' -')) {
            $container = Str::before($container, ' -');
        }

        $this->docker->stopContainer($container);
    }

    private function loadMenu($stoppableContainers)
    {
        return select(
            label: self::MENU_TITLE,
            options: $stoppableContainers
        );
    }
}
