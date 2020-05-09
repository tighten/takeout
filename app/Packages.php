<?php

namespace App;

use Illuminate\Support\Arr;

class Services
{
    protected $services = [
        'mysql' => \App\Services\MySql::class,
        'meilisearch' => \App\Services\MeiliSearch::class,
    ];

    public function construct()
    {
        // iterate over all service classes (except BaseService)
        // and build a 'shortname' => 'fqcn' array and store in
        // this->services
    }

    public function all()
    {
        return $this->services;
    }

    public function get(string $serviceName)
    {
        // @todo error if DNE?
        return Arr::get($this->services, $serviceName);
    }

    public function exists(string $serviceName)
    {
        return array_key_exists($serviceName, $this->services);
    }
}
