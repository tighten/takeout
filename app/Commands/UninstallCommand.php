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

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
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

    public function uninstallByServiceName(string $service)
    {
        $serviceMatches = collect($this->uninstallableServices)
            ->filter(function ($containerName, $containerId) use ($service) {
                return substr($containerName, 0, strlen($service)) === $service;
            });

        switch ($serviceMatches->count()) {
            case 0:
                return $this->error("\nCannot find a Takeout-managed instance of {$service}.");
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

    public function uninstallByContainerId(string $containerId)
    {
        try {
            $docker = app(Docker::class);
            $volumeName = $docker->attachedVolumeName($containerId);
            $docker->removeContainer($containerId);
            $this->info("\nService uninstalled.");
            if ($volumeName) {
                $this->info("\nThe uninstalled service was using a volume named {$volumeName}. If you would like to remove this data, run:");
                $this->info("\n docker volume rm {$volumeName}");
            }
        } catch (Throwable $e) {
            $this->error('Uninstallation failed!');
        }
    }

    public function uninstallableServices(): array
    {
        $services = app(Docker::class)->containers();
        array_shift($services);

        return collect($services)->mapWithKeys(function ($line) {
            return [$line[0] => ucwords(str_replace(['TO--', '--'], ['', ' '], $line[1]))];
        })->toArray();
    }
}
