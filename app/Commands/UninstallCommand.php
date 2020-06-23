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

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->initializeCommand();

        $service = $this->argument('serviceName');

        if ($service) {
            return $this->uninstallByServiceName($service);
        }

        $option = $this->menu('Services for uninstall', $this->uninstallableServices())->open();

        if (! $option) {
            return;
        }

        $this->uninstallByContainerId($option);
    }

    public function uninstallByServiceName(string $service)
    {
        $serviceMatches = collect($this->uninstallableServices())->filter(function ($containerName, $containerId) use ($service) {
            return substr($containerName, 0, strlen($service)) === $service;
        });

        if ($serviceMatches->count() > 1) {
            dd('We cannot handle multiple instances yet. @todo');
        }

        if ($serviceMatches->count() === 0) {
            $this->error("\nCannot find a Takeout-managed instance of {$service}.");
            return;
        }

        $this->uninstallByContainerId($serviceMatches->flip()->first());
    }

    public function uninstallByContainerId(string $containerId)
    {
        try {
            app(Docker::class)->removeContainer($containerId);
        } catch (Throwable $e) {
            $this->error('Uninstallation failed!');
        }

        $this->info("\nService uninstalled.");
    }

    public function uninstallableServices(): array
    {
        $services = app(Docker::class)->containers();
        array_shift($services);

        // @todo look up the fancy names maybe?
        return collect($services)->mapWithKeys(function ($line) {
            return [$line[0] => str_replace('TO--', '', $line[1])];
        })->toArray();
    }
}
