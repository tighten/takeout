<?php

namespace App\Commands;

use App\InitializesCommands;
use App\Shell\Docker;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;
use PhpSchool\CliMenu\CliMenu;

class StopCommand extends Command
{
    use InitializesCommands;

    protected $signature = 'stop {containerId?}';
    protected $description = 'Stop a service.';
    protected $docker;

    public function handle(Docker $docker): void
    {
        $this->docker = $docker;
        $this->initializeCommand();

        $container = $this->argument('containerId');

        if ($container) {
            $this->stop($container);

            return;
        }

        $this->menu('Containers to stop')
            ->addItems($this->stoppableContainers())
            ->open();
    }

    public function stoppableContainers(): array
    {
        return $this->docker->stoppableTakeoutContainers()->map(function ($container) {
            $label = sprintf('%s - %s', $container['container_id'], $container['names']);

            return [
                $label,
                function (CliMenu $menu) use ($container, $label) {
                    $this->stop($menu->getSelectedItem()->getText());

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

    public function stop(string $container): void
    {
        if (Str::contains($container, ' -')) {
            $container = Str::before($container, ' -');
        }

        $this->docker->stopContainer($container);
    }
}
