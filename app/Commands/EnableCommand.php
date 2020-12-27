<?php

namespace App\Commands;

use App\InitializesCommands;
use App\Services;
use App\Shell\Docker;
use App\Shell\Environment;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;

class EnableCommand extends Command
{
    use InitializesCommands;

    protected $signature = 'enable {serviceNames?*} {--default}';
    protected $description = 'Enable services.';
    protected $docker;
    protected $environment;
    protected $services;

    public function handle(Docker $docker, Environment $environment, Services $services): void
    {
        $this->docker = $docker;
        $this->environment = $environment;
        $this->services = $services;
        $this->initializeCommand();

        $services = $this->argument('serviceNames');

        $useDefaults = $this->option('default');

        if (filled($services)) {
            foreach ($services as $service) {
                $this->enable($service, $useDefaults);
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

        $this->enable($option, $useDefaults);
    }

    public function enableableServices(): array
    {
        return collect($this->services->all())->mapWithKeys(function ($fqcn, $shortName) {
            return [$shortName => $fqcn::name()];
        })->toArray();
    }

    public function enableableServicesByCategory(): array
    {
        return collect($this->services->all())
            ->mapToGroups(function ($fqcn, $shortName) {
                return [
                    $fqcn::category() => [
                        'shortName' => $shortName,
                        'name' => $fqcn::name(),
                    ],
                ];
            })
            ->sortKeys()
            ->toArray();
    }

    public function enable(string $service, bool $useDefaults = false): void
    {
        $fqcn = $this->services->get($service);
        app($fqcn)->enable($useDefaults);
    }
}
