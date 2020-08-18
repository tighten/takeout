<?php

namespace App\Commands;

use App\InitializesCommands;
use App\Services;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;

class InstallCommand extends Command
{
    use InitializesCommands;

    /**
     * The signature of the command.
     */
    protected $signature = 'install {serviceName?}';

    /**
     * The description of the command.
     */
    protected $description = 'Install a service.';

    public function handle(): void
    {
        $this->initializeCommand();

        $service = $this->argument('serviceName');

        if ($service) {
            $this->install($service);
            return;
        }

        $option = $this->menu('Services for install', $this->installableServices())->open();

        if (! $option) {
            return;
        }

        $this->install($option);
    }

    public function installableServices(): array
    {
        return collect((new Services)->all())->mapWithKeys(function ($fqcn, $shortName) {
            return [$shortName => Str::afterLast($fqcn, '\\')];
        })->toArray();
    }

    public function install(string $service): void
    {
        $fqcn = (new Services)->get($service);
        app($fqcn)->install();
    }
}
