<?php

namespace App\Commands;

use App\InitializesCommands;
use App\Shell\Docker;
use LaravelZero\Framework\Commands\Command;
use Throwable;

class UninstallCommand extends Command
{
    use InitializesCommands;

    /**
     * The signature of the command.
     */
    protected $signature = 'uninstall {serviceName?}';

    /**
     * The description of the command.
     */
    protected $description = 'Uninstall a service.';

    protected $uninstallableServices;
    protected $docker;

    public function handle(Docker $docker)
    {
        $this->docker = $docker;
        $this->initializeCommand();
        $this->uninstallableServices = $this->uninstallableServices();

        $service = $this->argument('serviceName');

        if ($service) {
            return $this->uninstallByServiceName($service);
        }

        $serviceContainerId = $this->menu('Services for uninstall', $this->uninstallableServices)->open();

        if (! $serviceContainerId) {
            return;
        }

        $this->uninstallByContainerId($serviceContainerId);
    }

    public function uninstallableServices(): array
    {
        return collect($this->docker->containers())->skip(1)->mapWithKeys(function ($line) {
            return [$line[0] => str_replace('TO--', '', $line[1])];
        })->toArray();
    }

    public function uninstallByServiceName(string $service): void
    {
        $serviceMatches = collect($this->uninstallableServices)
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
                $serviceContainerId = $this->menu('Select which service to uninstall.', $serviceMatches->toArray())->open();

                if (! $serviceContainerId) {
                    return;
                }
        }

        $this->uninstallByContainerId($serviceContainerId);
    }

    public function uninstallByContainerId(string $containerId): void
    {
        try {
            $this->docker->removeContainer($containerId);
        } catch (Throwable $e) {
            $this->error('Uninstallation failed!');
        }

        $this->info("\nService uninstalled.");
    }
}
