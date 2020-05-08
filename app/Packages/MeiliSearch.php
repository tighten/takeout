<?php

namespace App\Packages;

class MeiliSearch extends BasePackage
{
    // @todo
    // I assume this isn't what it will be in the end.
    // ... assuming we can at least drop "docker run",
    // but can we also extract any of the other params?
    // And possibly extract out some of the ports and
    // stuff to be configurable?
    protected $install = '-it --rm \
        -p 7700:7700 \
        -v meili_data:/data.ms \
        getmeili/meilisearch';
}
