<?php

namespace App\Services;

class MeiliSearch extends BaseService
{
    protected $defaultPort = 7700;
    protected $prompts = [
        [
            'shortname' => 'volume',
            'prompt' => 'What is the Docker volume name?',
            'default' => 'meili_data',
        ]
    ];

    protected $install = '-p {port}:7700 \
        -v {volume}:/data.ms \
        getmeili/meilisearch:{tag}';
}
