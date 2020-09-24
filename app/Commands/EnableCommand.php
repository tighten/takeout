<?php

namespace App\Commands;

use App\InitializesCommands;
use App\Services;
use LaravelZero\Framework\Commands\Command;

class EnableCommand extends Command
{
    use InitializesCommands;

    protected $signature = 'enable {serviceName?}';
    protected $description = 'Enable a service.';

    public function handle(): void
    {
        $this->initializeCommand();

        $service = $this->argument('serviceName');

        if ($service) {
            $this->enable($service);
            return;
        }

        $option = $this->menu('Services to enable:')->setTitleSeparator('=');

        foreach ($this->enableableServicesByCategory() as $category => $services) {
            $menuItems = collect($services)->mapWithKeys(function ($service) {
                return [$service['shortName'] => $service['name']];
            })->toArray();

            $option->addStaticItem("{$category}:")
                ->addStaticItem('---------')
                ->addOptions($menuItems)
                ->addLineBreak('', 1);
        }

        $option = $option->open();

        if (! $option) {
            return;
        }

        $this->enable($option);
    }

    public function enableableServices(): array
    {
        return collect((new Services)->all())->mapWithKeys(function ($fqcn, $shortName) {
            return [$shortName => $fqcn::name()];
        })->toArray();
    }

    public function enableableServicesByCategory(): array
    {
        return collect((new Services)->all())
            ->mapToGroups(function ($fqcn, $shortName) {
                return [
                    $fqcn::category() => [
                        'shortName' => $shortName,
                        'name' => $fqcn::name(),
                    ]
                ];
            })->toArray();
    }

    public function enable($service): void
    {
        $fqcn = (new Services)->get($service);
        app($fqcn)->enable();
    }
}
