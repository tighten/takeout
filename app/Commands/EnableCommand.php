<?php

namespace App\Commands;

use App\InitializesCommands;
use App\Services;
use App\Shell\Environment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use LaravelZero\Framework\Commands\Command;
use function Laravel\Prompts\search;

class EnableCommand extends Command
{
    use InitializesCommands;

    const MENU_TITLE = 'Takeout containers to enable';

    protected $signature = 'enable {serviceNames?*} {--default} {--run= : Pass any extra docker run options.}';
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
        $runOptions = $this->option('run');

        if (filled($services)) {
            if ($runOptions && is_array($services) && count($services) > 1) {
                $this->error('The --run options should only be used for enabling a single service.');
                return;
            }

            foreach ($services as $service) {
                $this->enable($service, $useDefaults, $passthroughOptions, $runOptions);
            }

            return;
        }

        $service = $this->selectService($this->availableServices());

        $this->enable($service, $useDefaults, $passthroughOptions);
    }

    /**
     * Since we're pulling the *full* list of server arguments, not just relying on
     * $this->argument, we have to do our own manual overriding for testing scenarios,
     * because pulling $_SERVER['argv'] won't give the right results in testing.
     */
    private function serverArguments(): array
    {
        if (App::environment() === 'testing') {
            $string = array_merge(['takeout', 'enable'], $this->argument('serviceNames'));

            if ($this->option('default')) {
                $string[] = '--default';
            }

            if ($this->option('run')) {
                $string[] = '--run';
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
        $arguments = collect($arguments)
            ->reject(fn($argument) => str_starts_with($argument, '--') && strlen($argument) > 2)
            ->values()
            ->toArray();

        $start = array_search('enable', $arguments) + 1;

        if (in_array('--', $arguments)) {
            $length = array_search('--', $arguments) - $start;

            return array_slice($arguments, $start, $length);
        }

        return array_slice($arguments, $start);
    }

    private function availableServices(): Collection
    {
        return $this->enableableServicesByCategory()->flatMap(function ($services, $category) {
            return $this->menuItemsForServices($services)->mapWithKeys(function ($row, $key) use ($category) {
                return [$key => "{$category}: {$row}"];
            })->toArray();
        });
    }

    private function selectService(Collection $servicesList): ?string
    {
        return search(
            label: self::MENU_TITLE,
            options: fn(string $value) => strlen($value) > 0
                ? $servicesList->filter(function ($row) use ($value) {
                    return str($row)->lower()->contains(str($value)->lower());
                })->toArray()
                : $servicesList->toArray(),
            scroll: 10
        );
    }

    private function menuItemsForServices($services): Collection
    {
        return collect($services)->mapWithKeys(function ($service) {
            return [$service['shortName'] => $service['name']];
        });
    }

    private function enableableServicesByCategory(): Collection
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
            ->sortKeys();
    }

    private function enable(
        string $service,
        bool $useDefaults = false,
        array $passthroughOptions = [],
        string $runOptions = null
    ): void {
        $fqcn = $this->services->get($service);
        app($fqcn)->enable($useDefaults, $passthroughOptions, $runOptions);
    }
}
