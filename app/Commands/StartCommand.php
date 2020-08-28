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

    protected $signature = 'start {container?}';
    protected $description = 'Start a stopped container.';

    public function handle(): void
    {
        $this->initializeCommand();

        $container = $this->argument('container');

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
        return collect(app(Docker::class)->takeoutContainers())->skip(1)->reduce(function ($containers, $container) {
            if(!Str::contains($container[2], 'Up')) {
                $containers->push(["$container[0] - $container[1]", function(CliMenu $menu) use ($container) {
                    $this->start($menu->getSelectedItem()->getText());

                    foreach($menu->getItems() as $item) {
                        if($item->getText() === "$container[0] - $container[1]") {
                            $menu->removeItem($item);
                        }
                    }

                    $menu->redraw();
                }]);
            }

            return $containers;
        }, collect())->toArray();
    }

    public function start(string $container): void
    {
        if(Str::contains($container, ' -')) {
            $container = Str::before($container, ' -');
        }

        app(Docker::class)->startContainer($container);
    }
}
