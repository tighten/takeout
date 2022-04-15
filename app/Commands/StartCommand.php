<?php

namespace App\Commands;

use App\InitializesCommands;
use App\Shell\Docker;
use App\Shell\Environment;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;
use PhpSchool\CliMenu\CliMenu;

class StartCommand extends Command
{
    use InitializesCommands;

    const MENU_TITLE = 'Takeout containers to start';

    protected $signature = 'start {containerId?*} {--all}';
    protected $description = 'Start a stopped container.';
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
                $this->startByServiceNameOrContainerId($container);
            }

            return;
        }

        if ($this->option('all')) {
            foreach ($this->docker->startableTakeoutContainers() as $startableContainer) {
                $this->start($startableContainer['container_id']);
            }

            return;
        }

        if (! $startableContainers = $this->startableContainers()) {
            $this->info("No Takeout containers available to start.\n");

            return;
        }

        $this->loadMenu($startableContainers);
    }

    public function startableContainers(): array
    {
        return $this->docker->startableTakeoutContainers()->map(function ($container) {
            $label = sprintf('%s - %s', $container['container_id'], $container['names']);

            return [
                $label,
                $this->loadMenuItem($container, $label),
            ];
        }, collect())->toArray();
    }

    public function startByServiceNameOrContainerId(string $serviceNameOrContainerId): void
    {
        $containersByServiceName = $this->docker->startableTakeoutContainers()
            ->map(function ($container) {
                return [
                    'container' => $container,
                    'label' => str_replace('TO--', '', $container['names']),
                ];
            })
            ->filter(function ($item) use ($serviceNameOrContainerId) {
                return Str::startsWith($item['label'], $serviceNameOrContainerId);
            });

        // If we don't get any container by the service name, that probably means
        // the user is trying to start a container using its container ID, so
        // we will just forward that down to the underlying start method.

        if ($containersByServiceName->isEmpty()) {
            $this->start($serviceNameOrContainerId);

            return;
        }

        if ($containersByServiceName->count() === 1) {
            $this->start($containersByServiceName->first()['container']['container_id']);

            return;
        }

        $selectedItem = $this->loadMenu($containersByServiceName->map(function ($item) {
            $label = $item['container']['container_id'] . ' - ' . $item['label'];

            return [
                $label,
                $this->loadMenuItem($item['container'], $label),
            ];
        })->all());

        if (! $selectedItem) {
            return;
        }

        $this->start($selectedItem);
    }

    public function start(string $container): void
    {
        if (Str::contains($container, ' -')) {
            $container = Str::before($container, ' -');
        }

        $this->docker->startContainer($container);
    }

    private function loadMenu($startableContainers)
    {
        if ($this->environment->isWindowsOs()) {
            return $this->windowsMenu($startableContainers);
        }

        return $this->defaultMenu($startableContainers);
    }

    private function defaultMenu($startableContainers)
    {
        return $this->menu(self::MENU_TITLE)
            ->addItems($startableContainers)
            ->addLineBreak('', 1)
            ->open();
    }

    private function windowsMenu($startableContainers)
    {
        if (! $startableContainers) {
            return;
        }

        $choices = Arr::flatten($startableContainers);
        $choices = Arr::where($choices, function ($value, $key) {
            return is_string($value);
        });
        array_push($choices, '<info>Exit</>');

        $choice = $this->choice(self::MENU_TITLE, array_values($choices));

        if (Str::contains($choice, 'Exit')) {
            return;
        }

        $chosenStartableContainer = Arr::where($startableContainers, function ($value, $key) use ($choice) {
            return $value[0] === $choice;
        });

        return call_user_func(array_values($chosenStartableContainer)[0][1]);
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
            $this->start($label);

            $startableContainers = $this->startableContainers();

            return $this->windowsMenu($startableContainers);
        };
    }

    private function defaultMenuItem($container, $label): callable
    {
        return function (CliMenu $menu) use ($container, $label) {
            $this->start($menu->getSelectedItem()->getText());

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
