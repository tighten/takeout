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

    protected $signature = 'start {containerId?}';
    protected $description = 'Start a stopped container.';
    protected $docker;
    protected $environment;

    public function handle(Docker $docker, Environment $environment): void
    {
        $this->docker = $docker;
        $this->environment = $environment;
        $this->initializeCommand();

        $container = $this->argument('containerId');

        if ($container) {
            $this->start($container);

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

    public function start(string $container): void
    {
        if (Str::contains($container, ' -')) {
            $container = Str::before($container, ' -');
        }

        $this->docker->startContainer($container);
    }

    private function loadMenu($startableContainers): void
    {
        if ($this->environment->isWindowsOs()) {
            $this->windowsMenu($startableContainers);

            return;
        }

        $this->defaultMenu($startableContainers);
    }

    private function defaultMenu($startableContainers)
    {
        $this->menu(self::MENU_TITLE)
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

        call_user_func(array_values($chosenStartableContainer)[0][1]);
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

            if (! Arr::has($menu->getItems(), ['use.label'])) {
                $menu->close();

                return;
            }
            $menu->redraw();
        };
    }
}
