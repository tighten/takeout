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
    protected $signature = 'list:containers';

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
        $containers = app(Docker::class)->containers()->getOutput();
        $lines =  explode("\n", $containers);
        $all = array_map(function ($line) {
            return array_filter(explode("        ", $line));
        }, $lines);
        $headers = array_shift($all);
        $this->table($headers, $all);
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
