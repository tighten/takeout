<?php

namespace App\Commands;

use App\InstallPackage;
use LaravelZero\Framework\Commands\Command;

class InstallCommand extends Command
{
    /**
     * The signature of the command.
     */
    protected $signature = 'install {packageName?}';

    /**
     * The description of the command.
     */
    protected $description = 'Install a package.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        app()->bind('console', function () {
            return $this;
        });

        $package = $this->argument('packageName');

        if ($package) {
            // @todo validate it exists in Packages::all
            return $this->install($package);
        }

        // @todo build this from available packages--maybe just use Packages:all but trim the FQCN?
        $option = $this->menu('Packages for install', [
            'mysql' => 'MySQL',
            'meilisearch' => 'MeiliSearch',
        ])->open();

        return $this->install($option);
    }

    public function install(string $package)
    {
        (new InstallPackage)($package);
    }
}
