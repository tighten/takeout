<?php

namespace App\Commands;

use App\InitializesCommands;
use App\Services;
use App\Shell\Environment;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;

class EnableCommand extends Command
{
    use InitializesCommands;

    const MENU_TITLE = 'Takeout containers to enable';

    protected $signature = 'enable {serviceNames?*} {--default}';
    protected $description = 'Enable services.';
    protected $environment;
    protected $services;

    public function handle(Environment $environment, Services $services): void
    {
        $this->environment = $environment;
        $this->services = $services;
        $this->initializeCommand();

        $services = $this->removeOptions($this->serverArguments());
        $passthroughOptions = $this->extractPassthroughOptions($this->serverArguments());

        $useDefaults = $this->option('default');

        if (filled($services)) {
            foreach ($services as $service) {
                $this->enable($service, $useDefaults, $passthroughOptions);
            }

            return;
        }

        $option = $this->selectService();

        if (! $option) {
            return;
        }

        $this->enable($option, $useDefaults, $passthroughOptions);
    }

    /**
     * Since we're pulling the *full* list of server arguments, not just relying on
     * $this->argument, we have to do our own manual overriding for testing scenarios,
     * because pulling $_SERVER['argv'] won't give the right results in testing.
     */
    public function serverArguments(): array
    {
        if (App::environment() === 'testing') {
            $string = array_merge(['takeout', 'enable'], $this->argument('serviceNames'));

            if ($this->option('default')) {
                $string[] = '--default';
            }

            return $string;
        }

        return $_SERVER['argv'];
    }

    /**
     * Extract and return any passthrough options from the parameters list
     *
     * @param array $arguments
     * @return array
     */
    public function extractPassthroughOptions(array $arguments): array
    {
        if (! in_array('--', $arguments)) {
            return [];
        }

        return array_slice($arguments, array_search('--', $arguments) + 1);
    }

    /**
     * Remove any options or passthrough options from the parameters list, returning
     * just the parameters passed to `enable`
     *
     * @param array $arguments
     * @return array
     */
    public function removeOptions(array $arguments): array
    {
        $arguments = collect($arguments)->reject(fn ($argument) => str_starts_with($argument, '--') && strlen($argument) > 2)->values()->toArray();

        $start = array_search('enable', $arguments) + 1;

        if (in_array('--', $arguments)) {
            $length = array_search('--', $arguments) - $start;

            return array_slice($arguments, $start, $length);
        }

        return array_slice($arguments, $start);
    }

    private function selectService(): ?string
    {
        if ($this->environment->isWindowsOs()) {
            return $this->windowsMenu();
        }

        return $this->defaultMenu();
    }

    private function defaultMenu(): ?string
    {
        $option = $this->menu(self::MENU_TITLE)->setTitleSeparator('=');

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

        $choice = $this->choice(self::MENU_TITLE, $choices);

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

    public function enable(string $service, bool $useDefaults = false, array $passthroughOptions = []): void
    {
        $fqcn = $this->services->get($service);
        app($fqcn)->enable($useDefaults, $passthroughOptions);
    }
}
