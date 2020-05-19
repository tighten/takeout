<?php

namespace App\Commands;

use App\InitializesCommands;
use App\Shell\Docker;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class ListCommand extends Command
{
    use InitializesCommands;

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'list';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'List all services installed by Takeout.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->initializeCommand();

        // @todo run Jose's command and format it in a nice Symfony table
        dd((new Docker())->containers());

        // @todo test this call
    }

    /**
     * Define the command's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
