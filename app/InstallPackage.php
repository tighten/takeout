<?php

namespace App;

use App\Services;
use App\Services\BaseService;

class InstallService
{
    public function __invoke(string $serviceName)
    {
        $service = $this->serviceForName($serviceName);
        $service->install();

        // @todo is this class even necessary any more since we're
        // letting services install() themselves? I guess it's really
        // a mapper from short string to class ¯\(°_o)/¯
    }

    public function serviceForName(string $serviceName): BaseService
    {
        foreach ((new Services)->all() as $shortname => $fqcn) {
            if ($shortname === $serviceName) {
                return new $fqcn;
            }
        }

        // @todo handle this better
        dd('Fail! No match for ' . $serviceName);
    }
}
