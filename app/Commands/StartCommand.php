<?php

namespace App\Commands;

use App\InitializesCommands;
use App\Shell\Docker;
use App\Shell\Environment;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;

use function Laravel\Prompts\select;

class StartCommand extends Command
{
    use InitializesCommands;

    const MENU_TITLE = 'Takeout containers to start';

    protected $signature = 'start {containerId?*} {--all}';
    protected $description = 'Start a stopped container.';
    protected $docker;
    protected $environment;

    public function handle(Docker $docker, Environment $environment): void
    {
        $this->docker = $docker;
        $this->environment = $environment;
        $this->initializeCommand();
        $startableContainers = $this->startableContainers();

        if ($this->option('all')) {
            $startableContainers->keys()->each(function ($containerId) {
                $this->start($containerId);
            });

            return;
        }

        if ($startableContainers->isEmpty()) {
            $this->info("No Takeout containers available to start.\n");

            return;
        }

        if (filled($services = $this->argument('containerId'))) {
            foreach ($services as $service) {
                $this->startByServiceNameOrContainerId($service, $startableContainers);
            }

            return;
        }

        $this->start($this->selectOptions($startableContainers));
    }

    public function startableContainers(): Collection
    {
        return $this->docker->startableTakeoutContainers()->mapWithKeys(function ($container) {
            return [$container['container_id'] => str_replace('TO--', '', $container['names'])];
        });
    }

    public function startByServiceNameOrContainerId(string $service, Collection $startableContainers): void
    {
        $containersByServiceName = $startableContainers
            ->filter(function ($serviceName, $key) use ($service) {
                return Str::startsWith($serviceName, $service) || "{$key}" === $service;
            });

        // If we don't get any container by the service name, that probably means
        // the user is trying to start a container using its container ID, so
        // we will just forward that down to the underlying start method.
        if ($containersByServiceName->isEmpty()) {
            $this->info('No containers found for ' . $service);

            return;
        }

        if ($containersByServiceName->count() === 1) {
            $this->start($containersByServiceName->keys()->first());

            return;
        }

        $this->start($this->selectOptions($containersByServiceName));
    }

    public function start(string $container): void
    {
        if (Str::contains($container, ' -')) {
            $container = Str::before($container, ' -');
        }

        $this->docker->startContainer($container);
    }

    private function selectOptions(Collection $startableContainers)
    {
        return select(
            label: self::MENU_TITLE,
            options: $startableContainers
        );
    }
}
