<?php

namespace App\Commands;

use App\InitializesCommands;
use App\Shell\Docker;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;
use PhpSchool\CliMenu\CliMenu;

class StartCommand extends Command
{
    use InitializesCommands;

    protected $signature = 'start {containerId?}';
    protected $description = 'Start a stopped container.';

    protected $docker;

    public function __construct(Docker $docker)
    {
        $this->docker = $docker;

        parent::__construct();
    }

    public function handle(): void
    {
        $this->initializeCommand();

        if ($this->argument('containerId')) {
            $this->start($this->argument('containerid'));

            return;
        }

        $this->menu('Containers to start')
            ->addItems($this->startableContainers())
            ->open();
    }

    public function startableContainers(): array
    {
        return collect($this->docker->takeoutContainers())->skip(1)->reject(function($container) {
            return Str::contains($container[2], 'Up');
        })->map(function ($container) {
            return [
                "$container[1] (ID: $container[0])",
                function (CliMenu $menu) use ($container) {
                    $this->start($container[0]);

                    $this->redrawMenuExcludingCurrent($menu);
                }
            ];
        })->toArray();
    }

    public function start(string $containerId): void
    {
        dd($containerId);
        $this->docker->startContainer(Str::before($container, ' -'));
    }

    protected function redrawMenuExcludingCurrent($menu)
    {
        foreach ($menu->getItems() as $index => $item) {
            if ($item === $menu->getSelectedItem()) {
                $menu->removeItem($item);
            }
        }

        $menu->redraw();
    }
}
