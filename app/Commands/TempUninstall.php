<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class TempUninstall extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'temp:uninstall';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Uninstall Takeout while it\'s not on Packagist.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (! file_exists('/usr/local/bin/takeout')) {
            echo "\nAlready removed!\n";
            exit;
        }

        exec('rm /usr/local/bin/takeout');

        echo "\nTakeout symlink removed from your /usr/local/bin directory.\n";
    }
}
