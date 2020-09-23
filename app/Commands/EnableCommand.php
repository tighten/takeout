<?php

namespace App\Commands;

use App\Services;
use App\InitializesCommands;
use LaravelZero\Framework\Commands\Command;

class EnableCommand extends Command
{
    use InitializesCommands;

    protected $signature = 'enable {serviceName?} {--default}';
    protected $description = 'Enable a service.';

    public function handle(): void
    {
        $this->initializeCommand();

        $service = $this->argument('serviceName');

        $useDefaults = $this->option('default');

        if ($service) {
            $this->enable($service, $useDefaults);

            return;
        }

        $option = $this->menu('Services to enable', $this->enableableServices())
            ->addLineBreak('', 1)
            ->setPadding(2, 5)
            ->open();

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

    public function enable(string $service, bool $useDefaults = false): void
    {
        $fqcn = (new Services)->get($service);
        app($fqcn)->enable($useDefaults);
    }
}
