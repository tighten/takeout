<?php

namespace App\Commands;

use App\InitializesCommands;
use App\Shell\Docker;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;
use PhpSchool\CliMenu\CliMenu;

class StartCommand extends Command
{
    use InitializesCommands;

    protected $signature = 'start {containerId?} {--all}';
    protected $description = 'Start a stopped container.';
    protected $docker;

    public function handle(Docker $docker): void
    {
        $this->docker = $docker;
        $this->initializeCommand();

        $container = $this->argument('containerId');

        if ($container) {
            $this->start($container);

            return;
        }

        if ($this->option('all')) {
            $this->startableContainerIds()->each(function (string $startableContainerId) {
                $this->start($startableContainerId);
            });

            return;
        }

        $this->menu('Containers to start')
            ->addItems($this->startableContainers())
            ->open();
    }

    public function startableContainerIds(): Collection
    {
        return $this->docker->startableTakeoutContainers()->pluck('container_id');
    }

    public function startableContainers(): array
    {
        return $this->docker->startableTakeoutContainers()->map(function ($container) {
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

        $this->docker->startContainer($container);
    }
}
