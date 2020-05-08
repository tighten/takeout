<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class InstallCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'install {packageName?}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Install a package.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $package = $this->argument('packageName');

        if ($package) {
            return $this->install($package);
        }

        // @todo build this from available packages
        $option = $this->menu('Packages for install', [
            'mysql' => 'MySQL',
            'meilisearch' => 'MeiliSearch',
        ])->open();

        return $this->install($option);
    }

    public function install(string $package)
    {
        $this->info('Purportedly installing ' . $package);
    }
}
