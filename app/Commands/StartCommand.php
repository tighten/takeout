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

    protected $signature = 'start {containerId?*} {--all}';
    protected $description = 'Start a stopped container.';

    public function handle(): void
    {
        $this->initializeCommand();

        $containers = $this->argument('containerId');

        if ($containers) {
            foreach ($containers as $container) {
                $this->start($container);
            }

            return;
        }

        if ($this->option('all')) {
            foreach (app(Docker::class)->startableTakeoutContainers() as $startableContainer) {
                $this->start($startableContainer['container_id']);
            }
            return;
        }

        $this->menu('Containers to start')
            ->addItems($this->startableContainers())
            ->open();
    }

    public function startableContainers(): array
    {
        return app(Docker::class)->startableTakeoutContainers()->map(function ($container) {
            $label = sprintf('%s - %s', $container['container_id'], $container['names']);

            return [
                $label,
                function (CliMenu $menu) use ($container, $label) {
                    $this->start($menu->getSelectedItem()->getText());

                    foreach ($menu->getItems() as $item) {
                        if ($item->getText() === $label) {
                            $menu->removeItem($item);
                        }
                    }

                    $menu->redraw();
                },
            ];
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
