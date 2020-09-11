<?php

namespace App\Commands;

use App\InitializesCommands;
use App\Services;
use App\Shell\Docker;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;
use PhpSchool\CliMenu\CliMenu;

class StartCommand extends Command
{
    use InitializesCommands;

    protected $signature = 'start {containerId?}';
    protected $description = 'Start a stopped container.';

    public function handle(): void
    {
        $this->initializeCommand();

        $container = $this->argument('containerId');

        if ($container) {
            $this->start($container);

            return;
        }

        $this->menu('Containers to start')
            ->addItems($this->startableContainers())
            ->open();
    }

    public function startableContainers(): array
    {
        return app(Docker::class)->takeoutContainers()->reject(function($container) {
            return Str::contains($container['status'], 'Up');
        })->map(function ($container) {
            $label = sprintf('%s - %s', $container['container_id'], $container['names']);

            return [$label, function(CliMenu $menu) use ($container, $label) {
                $this->start($menu->getSelectedItem()->getText());

                foreach ($menu->getItems() as $item) {
                    if ($item->getText() === $label) {
                        $menu->removeItem($item);
                    }
                }

                $menu->redraw();
            }];
        }, collect())->toArray();
    }

    public function start(string $container): void
    {
        if (Str::contains($container, ' -')) {
            $container = Str::before($container, ' -');
        }

        app(Docker::class)->startContainer($container);
    }
}
