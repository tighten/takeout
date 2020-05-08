<?php

namespace App;

use App\Packages;
use App\Packages\BasePackage;

class InstallPackage
{
    public function __invoke(string $packageName)
    {
        $package = $this->packageForName($packageName);
        $package->install();

        // @todo is this class even necessary any more since we're
        // letting packages install() themselves? I guess it's really
        // a mapper from short string to class ¯\(°_o)/¯
    }

    public function packageForName(string $packageName): BasePackage
    {
        foreach ((new Packages)->all() as $shortname => $fqcn) {
            if ($shortname === $packageName) {
                return new $fqcn;
            }
        }

        // @todo handle this better
        dd('Fail! No match for ' . $packageName);
    }
}
