<?php

namespace App\Commands;

use App\InitializesCommands;
use App\Services;
use App\Shell\Docker;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;
use PhpSchool\CliMenu\CliMenu;

class StopCommand extends Command
{

    use InitializesCommands;

    protected $signature = 'stop {container?}';
    protected $description = 'Stop a service.';

    public function handle(): void
    {
        $this->initializeCommand();

        $container = $this->argument('container');

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
        return collect(app(Docker::class)->takeoutContainers())->skip(1)->reduce(function ($containers, $container) {
            if(Str::contains($container[2], 'Up')) {
                $containers->push(["$container[0] - $container[1]", function(CliMenu $menu) use ($container) {
                    $this->stop($menu->getSelectedItem()->getText());

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

    public function stop(string $container): void
    {
        if(Str::contains($container, ' -')) {
            $container = Str::before($container, ' -');
        }

        app(Docker::class)->stopContainer($container);
    }
}
