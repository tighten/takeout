<?php

namespace App;

use Exception;
use Illuminate\Support\Arr;

class Services
{
    protected $services = [];

    public function __construct()
    {
        $this->services = collect($this->classesInServicesNamespace())->mapWithKeys(function ($fqcn, $a) {
            $instance = new $fqcn;
            return [$instance->shortName() => $fqcn];
        })->toArray();
    }

    public function all(): array
    {
        return $this->services;
    }

    public function classesInServicesNamespace(): array
    {
        return collect(scandir(base_path('app/Services')))->reject(function ($file) {
            return in_array($file, ['.', '..', 'BaseService.php']);
        })->map(function ($file) {
            return '\App\Services\\' . substr($file, 0, -4);
        })->toArray();
    }

    public function get(string $serviceName): string
    {
        if (! $this->exists($serviceName)) {
            throw new Exception('Service ' . $serviceName . ' is invalid.');
        }

        return Arr::get($this->services, $serviceName);
    }

    public function exists(string $serviceName): bool
    {
        return array_key_exists($serviceName, $this->services);
    }
}
