<?php

namespace App\Commands;

use App\InitializesCommands;
use App\Services;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;

class EnableCommand extends Command
{
    use InitializesCommands;

    protected $signature = 'enable {serviceNames?*} {--default}';
    protected $description = 'Enable services.';
    protected $services;

    public function handle(Services $services): void
    {
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

        $option = $this->selectService();

        if (! $option) {
            return;
        }

        $this->enable($option, $useDefaults);
    }

    private function selectService(): ?string
    {
        if (in_array(PHP_OS_FAMILY, ['Windows'])) {
            return $this->windowsMenu();
        }

        return $this->defaultMenu();
    }

    private function defaultMenu(): ?string
    {
        $option = $this->menu('Services to enable:')->setTitleSeparator('=');

        foreach ($this->enableableServicesByCategory() as $category => $services) {
            $separator = str_repeat('-', 1 + Str::length($category));

            $option->addStaticItem("{$category}:")
                ->addStaticItem($separator)
                ->addOptions($this->menuItemsForServices($services))
                ->addLineBreak('', 1);
        }

        return $option->open();
    }

    private function windowsMenu($category = null): ?string
    {
        $choices = [];
        $groupedServices = $this->enableableServicesByCategory();

        if ($category) {
            $groupedServices = Arr::where($groupedServices, function ($value, $key) use ($category) {
                return Str::contains($category, strtoupper($key));
            });
        }

        foreach ($groupedServices as $serviceCategory => $services) {
            $serviceCategoryMenuItem = '<fg=white;bg=blue;options=bold> ' . (Str::upper($serviceCategory)) . ' </>';
            array_push($choices, $serviceCategoryMenuItem);

            foreach ($this->menuItemsForServices($services) as $menuItemKey => $menuItemName) {
                array_push($choices, $menuItemName);
            }
        }

        if ($category) {
            array_push($choices, '<info>Back</>');
        }

        array_push($choices, '<info>Exit</>');

        $choice = $this->choice('What service would you like to enable?', $choices);

        if (Str::contains($choice, 'Back')) {
            return $this->windowsMenu();
        }

        if (Str::contains($choice, 'Exit')) {
            return null;
        }

        foreach ($this->enableableServices() as $shortName => $fqcn) {
            if ($choice === $fqcn) {
                return $shortName;
            }
        }

        return $this->windowsMenu($choice);
    }

    private function menuItemsForServices($services): array
    {
        return collect($services)->mapWithKeys(function ($service) {
            return [$service['shortName'] => $service['name']];
        })->toArray();
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
