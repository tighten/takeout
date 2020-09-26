<?php

namespace App\Commands;

use App\InitializesCommands;
use App\Services;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;

class EnableCommand extends Command
{
    use InitializesCommands;

    protected $signature = 'enable {serviceNames?*}';
    protected $description = 'Enable services.';

    public function handle(): void
    {
        $this->initializeCommand();

        $services = $this->argument('serviceNames');

        if (filled($services)) {
            foreach ($services as $service) {
                $this->enable($service);
            }

            return;
        }

        $option = $this->menu('Services to enable:')->setTitleSeparator('=');

        foreach ($this->enableableServicesByCategory() as $category => $services) {
            $menuItems = collect($services)->mapWithKeys(function ($service) {
                return [$service['shortName'] => $service['name']];
            })->toArray();

            $separator = str_repeat('-', 1 + Str::length($category));

            $option->addStaticItem("{$category}:")
                ->addStaticItem($separator)
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
