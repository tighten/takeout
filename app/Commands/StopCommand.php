<?php

namespace App\Commands;

use App\InitializesCommands;
use App\Shell\Docker;
use App\Shell\Environment;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;
use PhpSchool\CliMenu\CliMenu;

class StopCommand extends Command
{
    use InitializesCommands;

    const MENU_TITLE = 'Takeout containers to stop';

    protected $signature = 'stop {containerId?*} {--all}';
    protected $description = 'Stop one ore more started containers.';
    protected $docker;
    protected $environment;

    public function handle(Docker $docker, Environment $environment): void
    {
        $this->docker = $docker;
        $this->environment = $environment;
        $this->initializeCommand();

        $containers = $this->argument('containerId');

        if (filled($containers)) {
            foreach ($containers as $container) {
                $this->stopByServiceNameOrContainerId($container);
            }

            return;
        }

        if ($this->option('all')) {
            foreach ($this->docker->stoppableTakeoutContainers() as $stoppableContainer) {
                $this->stop($stoppableContainer['container_id']);
            }

            return;
        }

        if (! $stoppableContainers = $this->stoppableContainers()) {
            $this->info("No Takeout containers available to stop.\n");

            return;
        }

        $this->loadMenu($stoppableContainers);
    }

    public function stoppableContainers(): array
    {
        return $this->docker->stoppableTakeoutContainers()->map(function ($container) {
            $label = sprintf('%s - %s', $container['container_id'], $container['names']);

            return [
                $label,
                $this->loadMenuItem($container, $label),
            ];
        }, collect())->toArray();
    }

    public function stopByServiceNameOrContainerId(string $serviceNameOrContainerId): void
    {
        $containersByServiceName = $this->docker->stoppableTakeoutContainers()
            ->map(function ($container) {
                return [
                    'container' => $container,
                    'label' => str_replace('TO--', '', $container['names']),
                ];
            })
            ->filter(function ($item) use ($serviceNameOrContainerId) {
                return Str::startsWith($item['label'], $serviceNameOrContainerId);
            });

        if ($containersByServiceName->isEmpty()) {
            $this->start($serviceNameOrContainerId);

            return;
        }

        if ($containersByServiceName->count() === 1) {
            $this->stop($containersByServiceName->first()['container']['container_id']);
            return;
        }

        $selectedContainer = $this->loadMenu($containersByServiceName->map(function ($item) {
            $label = $item['container']['container_id'] . ' - ' . $item['label'];

            return [
                $label,
                $this->loadMenuItem($item['container'], $label),
            ];
        })->all());

        if (! $selectedContainer) return;

        $this->stop($selectedContainer);
    }

    public function stop(string $container): void
    {
        if (Str::contains($container, ' -')) {
            $container = Str::before($container, ' -');
        }

        $this->docker->stopContainer($container);
    }

    private function loadMenu($stoppableContainers)
    {
        if ($this->environment->isWindowsOs()) {
            return $this->windowsMenu($stoppableContainers);
        }

        return $this->defaultMenu($stoppableContainers);
    }

    private function defaultMenu($stoppableContainers)
    {
        return $this->menu(self::MENU_TITLE)
            ->addItems($stoppableContainers)
            ->addLineBreak('', 1)
            ->open();
    }

    private function windowsMenu($stoppableContainers)
    {
        if (! $stoppableContainers) {
            return;
        }

        $choices = Arr::flatten($stoppableContainers);
        $choices = Arr::where($choices, function ($value, $key) {
            return is_string($value);
        });
        array_push($choices, '<info>Exit</>');

        $choice = $this->choice(self::MENU_TITLE, array_values($choices));

        if (Str::contains($choice, 'Exit')) {
            return;
        }

        $chosenStoppableContainer = Arr::where($stoppableContainers, function ($value, $key) use ($choice) {
            return $value[0] === $choice;
        });

        return call_user_func(array_values($chosenStoppableContainer)[0][1]);
    }

    private function loadMenuItem($container, $label): callable
    {
        if ($this->environment->isWindowsOs()) {
            return $this->windowsMenuItem($container, $label);
        }

        return $this->defaultMenuItem($container, $label);
    }

    private function windowsMenuItem($container, $label): callable
    {
        return function () use ($container, $label) {
            $this->stop($label);

            $stoppableContainers = $this->stoppableContainers();

            return $this->windowsMenu($stoppableContainers);
        };
    }

    private function defaultMenuItem($container, $label): callable
    {
        return function (CliMenu $menu) use ($container, $label) {
            $this->stop($menu->getSelectedItem()->getText());

            foreach ($menu->getItems() as $item) {
                if ($item->getText() === $label) {
                    $menu->removeItem($item);
                }
            }

            if (count($menu->getItems()) === 3) {
                $menu->close();

                return;
            }

            $menu->redraw();
        };
    }
}
