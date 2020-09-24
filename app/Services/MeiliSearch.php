<?php

namespace App\Services;

class MeiliSearch extends BaseService
{
    protected $organization = 'getmeili';
    protected $imageName = 'meilisearch';
    protected static $category = 'search';
    protected $defaultPort = 7700;
    protected $prompts = [
        [
            'shortname' => 'volume',
            'prompt' => 'What is the Docker volume name?',
            'default' => 'meili_data',
        ],
    ];

    protected $dockerRunTemplate = '-p "${:port}":7700 \
        -v "${:volume}":/data.ms \
        "${:organization}"/"${:image_name}":"${:tag}"';
}
