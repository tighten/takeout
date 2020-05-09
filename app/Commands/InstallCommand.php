<?php

namespace App\Commands;

use App\Services;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;

class InstallCommand extends Command
{
    /**
     * The signature of the command.
     */
    protected $signature = 'install {serviceName?}';

    /**
     * The description of the command.
     */
    protected $description = 'Install a service.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        /** This section probably should be extracted so every command can use it */
        app()->bind('console', function () {
            return $this;
        });

        // @todo check if docker is installed
        /** end extraction area */

        $service = $this->argument('serviceName');

        if ($service) {
            return $this->install($service);
        }

        $option = $this->menu('Services for install', $this->installableServices())->open();

        return $this->install($option);
    }

    public function installableServices()
    {
        return collect((new Services)->all())->mapWithKeys(function ($fqcn, $shortName) {
            return [$shortName => Str::afterLast($fqcn, '\\')];
        })->toArray();
    }

    public function install(string $service)
    {
        $fqcn = (new Services)->get($service);
        $service = new $fqcn;
        $service->install();
    }
}
