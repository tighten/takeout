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
    protected $signature = 'list:services';

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

        $containers = app(Docker::class)->containers();

        $this->table(array_shift($containers), $containers);
    }
}
