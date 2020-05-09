<?php

namespace App\Commands;

use App\InstallService;
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

        // @todo build this from available services--maybe just use Services:all but trim the FQCN?
        $option = $this->menu('Services for install', [
            'mysql' => 'MySQL',
            'meilisearch' => 'MeiliSearch',
        ])->open();

        return $this->install($option);
    }

    public function install(string $service)
    {
        // @todo validate it exists in Services::all
        (new InstallService)($service);
    }
}
