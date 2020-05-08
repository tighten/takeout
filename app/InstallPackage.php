<?php

namespace App;

use App\Packages\BasePackage;

class InstallPackage
{
    public function __construct()
    {

    }

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
        // Loop over all packages; convert the class name to lowercase; compare against package name and make sure it's not basepackage
        return new \App\Packages\MeiliSearch; // @todo
    }
}
