<?php

namespace App\Commands;

use App\InitializesCommands;
use App\Shell\Docker;
use App\Shell\Environment;
use LaravelZero\Framework\Commands\Command;
use Throwable;

class DisableCommand extends Command
{
    use InitializesCommands;

    const MENU_TITLE = 'Takeout containers to disable';

    protected $signature = 'disable {serviceNames?*} {--all}';
    protected $description = 'Disable services.';
    protected $disableableServices;
    protected $docker;
    protected $environment;

    public function handle(Docker $docker, Environment $environment)
    {
        $this->docker = $docker;
        $this->environment = $environment;
        $this->initializeCommand();
        $this->disableableServices = $this->disableableServices();

        if ($this->option('all')) {
            foreach ($this->disableableServices as $containerId => $name) {
                $this->disableByContainerId($containerId);
            }

            return;
        }

        if (empty($this->disableableServices)) {
            $this->info("There are no containers to disable.\n");

            return;
        }

        if (filled($services = $this->argument('serviceNames'))) {
            foreach ($services as $service) {
                $this->disableByServiceName($service);
            }

            return;
        }

        $this->showDisableServiceMenu();
    }

    public function disableableServices(): array
    {
        return $this->docker->takeoutContainers()->mapWithKeys(function ($container) {
            return [$container['container_id'] => str_replace('TO--', '', $container['names'])];
        })->toArray();
    }

    public function disableByServiceName(string $service): void
    {
        $serviceMatches = collect($this->disableableServices)
            ->filter(function ($containerName) use ($service) {
                return substr($containerName, 0, strlen($service)) === $service;
            });

        switch ($serviceMatches->count()) {
            case 0:
                $this->error("\nCannot find a Takeout-managed instance of {$service}.");

                return;
            case 1:
                $serviceContainerId = $serviceMatches->flip()->first();
                break;
            default: // > 1
                $serviceContainerId = $this->selectMenu($disableableServices ?? $this->disableableServices);

                if (! $serviceContainerId) {
                    return;
                }
        }

        $this->disableByContainerId($serviceContainerId);
    }

    public function showDisableServiceMenu($disableableServices = null): void
    {
        if ($serviceContainerId = $this->selectMenu($disableableServices ?? $this->disableableServices)) {
            $this->disableByContainerId($serviceContainerId);
        }
    }

    private function selectMenu($disableableServices): ?string
    {
        if ($this->environment->isWindowsOs()) {
            return $this->windowsMenu($disableableServices);
        }

        return $this->defaultMenu($disableableServices);
    }

    private function defaultMenu($disableableServices): ?string
    {
        return $this->menu(self::MENU_TITLE, $disableableServices)
            ->addLineBreak('', 1)
            ->setPadding(2, 5)
            ->open();
    }

    private function windowsMenu($disableableServices): ?string
    {
        array_push($disableableServices, '<info>Exit</>');

        $choice = $this->choice(self::MENU_TITLE, array_values($disableableServices));

        return array_search($choice, $disableableServices);
    }

    public function disableByContainerId(string $containerId): void
    {
        try {
            $volumeName = $this->docker->attachedVolumeName($containerId);

            $this->task('Disabling Service...', $this->docker->removeContainer($containerId));

            if ($volumeName) {
                $this->info("\nThe disabled service was using a volume named {$volumeName}.");
                $this->info('If you would like to remove this data, run:');
                $this->info("\n docker volume rm {$volumeName}");
            }

            if (count($this->docker->allContainers()) === 0) {
                $question = 'No containers are running. Turn off Docker?';

                if ($this->environment->isWindowsOs()) {
                    $option = $this->confirm($question);
                } else {
                    $option = $this->menu($question, [
                        'Yes',
                        'No',
                    ])->disableDefaultItems()->open();
                }

                if ($option === 0 || $option === true) {
                    $this->task('Stopping Docker service ', $this->docker->stopDockerService());
                }
            }
        } catch (Throwable $e) {
            $this->error('Disabling failed! Error: ' . $e->getMessage());
        }
    }
}
