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
            // @todo allow for this with the tag removed
            return $this->uninstall($service);
        }

        $option = $this->menu('Services for uninstall', $this->uninstallableServices())->open();

        if (! $option) {
            return;
        }

        return $this->uninstall($this->uninstallableServices()[$option]);
    }

    public function uninstall(string $service)
    {
        $containers = $this->uninstallableServices();
        $containerId = array_search($service, $containers);

        if (! $containerId) {
            $this->error("\nCannot find a Takeout-managed instance of {$service}.");
            return;
        }

        try {
            app(Docker::class)->removeContainer($containerId);
        } catch (Throwable $e) {
            $this->error('Uninstallation failed!');
        }

        $this->info("\nService uninstalled.");
    }

    public function uninstallableServices()
    {
        $services = app(Docker::class)->containers();
        array_shift($services);

        // @todo look up the fancy names maybe?
        return collect($services)->mapWithKeys(function ($line) {
            return [$line[0] => str_replace('TO--', '', $line[1])];
        })->toArray();
    }
}
