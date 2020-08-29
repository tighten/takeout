<?php

namespace App\Commands;

use App\InitializesCommands;
use App\Services;
use Illuminate\Support\Str;
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
            return [$shortName => Str::afterLast($this->formatName($fqcn), '\\')];
        })->toArray();
    }

    public function enable(string $service): void
    {
        $fqcn = (new Services)->get($service);
        app($fqcn)->enable();
    }

    private function formatName(string $name): string
    {
        $search = ['MsSql', 'MySql', 'PostgreSql'];
        $replace = ['MS SQL', 'MySQL', 'PostgreSQL'];
        return Str::of($name)->replace($search, $replace);
    }
}
