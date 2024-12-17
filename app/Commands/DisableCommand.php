<?php

namespace App\Commands;

use App\InitializesCommands;
use App\Shell\Docker;
use App\Shell\Environment;
use Illuminate\Support\Collection;
use LaravelZero\Framework\Commands\Command;
use Throwable;
use Illuminate\Support\Str;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\select;

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

        $disableableServices = $this->disableableServices();

        if ($this->option('all')) {
            $disableableServices->keys()->each(function ($containerId) {
                $this->disableByContainerId($containerId);
            });

            return;
        }

        if ($disableableServices->isEmpty()) {
            $this->info("There are no containers to disable.\n");

            return;
        }

        if (filled($services = $this->argument('serviceNames'))) {
            foreach ($services as $service) {
                $this->disableByServiceName($service, $disableableServices);
            }

            return;
        }

        $this->disableByContainerId(
            $this->selectOptions($disableableServices),
        );
    }

    private function disableableServices(): Collection
    {
        return $this->docker->takeoutContainers()->mapWithKeys(function ($container) {
            return [$container['container_id'] => str_replace('TO--', '', $container['names'])];
        });
    }

    private function disableByServiceName(string $service, Collection $disableableServices): void
    {
        $serviceMatches = collect($disableableServices)
            ->filter(function ($containerName) use ($service) {
                return Str::startsWith($service, $containerName);
            });

        if ($serviceMatches->isEmpty()) {
            $this->error("\nCannot find a Takeout-managed instance of {$service}.");

            return;
        }

        if ($serviceMatches->count() === 1) {
            $this->disableByContainerId($serviceMatches->flip()->first());

            return;
        }

        $this->disableByContainerId(
            $this->selectOptions($disableableServices),
        );
    }

    private function selectOptions(Collection $disableableServices)
    {
        return select(
            label: self::MENU_TITLE,
            options: $disableableServices
        );
    }

    private function disableByContainerId(string $containerId): void
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

                if (confirm($question)) {
                    $this->task('Stopping Docker service ', $this->docker->stopDockerService());
                }
            }
        } catch (Throwable $e) {
            $this->error('Disabling failed! Error: ' . $e->getMessage());
        }
    }
}
