<?php

namespace App\Commands;

use App\InitializesCommands;
use App\Services;
use App\Shell\Docker;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;
use PhpSchool\CliMenu\CliMenu;

class RestartCommand extends Command
{

    use InitializesCommands;

    protected $signature = 'restart {container?}';
    protected $description = 'Restart a service.';

    public function handle(): void
    {
        $this->initializeCommand();

        $container = $this->argument('container');

        if ($container) {
            $this->restart($container);

            return;
        }

        $this->menu('Containers to restart')
            ->addItems($this->restartableContainers())
            ->open();

//        if (!$option) {
//            return;
//        }
//
//        $this->restart($option);
    }

    public function restartableContainers(): array
    {
        return collect(app(Docker::class)->takeoutContainers())->skip(1)->reduce(function ($containers, $container) {
            if(!Str::contains($container[2], 'Up')) {
                $containers->push(["$container[0] - $container[1]", function(CliMenu $menu) use ($container) {
                    $this->restart($menu->getSelectedItem()->getText());

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

    public function restart(string $container): void
    {
        if(Str::contains($container, ' -')) {
            $container = Str::before($container, ' -');
        }

        app(Docker::class)->startContainer($container);
    }
}
