<?php

namespace App;

class Packages
{
    protected $packages = [
        'mysql' => \App\Packages\MySql::class,
        'meilisearch' => \App\Packages\MeiliSearch::class,
    ];

    public function all()
    {
        return $this->packages;
    }
}
