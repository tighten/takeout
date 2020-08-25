<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class TempInstall extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'temp:install';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Install Takeout while it\'s not on Packagist.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (file_exists('/usr/local/bin/takeout')) {
            echo "\nAlready symlinked!\n";
            exit;
        }

        exec(sprintf(
            "ln -s %s %s",
            base_path() . '/takeout',
            '/usr/local/bin'
        ));

        echo "\nTakeout symlinked to your /usr/local/bin directory.\n";
    }
}
