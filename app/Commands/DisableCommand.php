<?php

namespace App\Commands;

use App\InitializesCommands;
use App\Shell\Docker;
use LaravelZero\Framework\Commands\Command;
use Throwable;

class DisableCommand extends Command
{
    use InitializesCommands;

    /**
     * The signature of the command.
     */
    protected $signature = 'disable {serviceName?}';

    /**
     * The description of the command.
     */
    protected $description = 'Disable a service.';

    protected $disableableServices;
    protected $docker;

    public function handle(Docker $docker)
    {
        $this->docker = $docker;
        $this->initializeCommand();
        $this->disableableServices = $this->disableableServices();

        if ($this->argument('serviceName')) {
            return $this->disableByServiceName($this->argument('serviceName'));
        }

        $this->showDisableServiceMenu();
    }

    public function disableableServices(): array
    {
        return collect($this->docker->containers())->skip(1)->mapWithKeys(function ($line) {
            return [$line[0] => str_replace('TO--', '', $line[1])];
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
                $serviceContainerId = $this->menu('Select which service to disable.', $serviceMatches->toArray())->open();

                if (! $serviceContainerId) {
                    return;
                }
        }

        $this->disableByContainerId($serviceContainerId);
    }

    public function showDisableServiceMenu(): void
    {
        $serviceContainerId = $this->menu('Services to disable', $this->disableableServices)->open();

        if ($serviceContainerId) {
            $this->disableByContainerId($serviceContainerId);
        }
    }

    public function disableByContainerId(string $containerId): void
    {
        try {
            $volumeName = $this->docker->attachedVolumeName($containerId);

            $this->docker->removeContainer($containerId);
            $this->info("\nService disabled.");

            if ($volumeName) {
                $this->info("\nThe disabled service was using a volume named {$volumeName}.");
                $this->info("If you would like to remove this data, run:");
                $this->info("\n docker volume rm {$volumeName}");
            }
        } catch (Throwable $e) {
            $this->error('Disabling failed!');
        }
    }
}
