<?php

namespace App\Services;

class Valkey extends BaseService
{
    protected static $category = Category::CACHE;

    protected $organization = 'valkey';
    protected $imageName = 'valkey';
    protected $defaultPort = 6379;
    protected $prompts = [
        [
            'shortname' => 'volume',
            'prompt' => 'What is the Docker volume name?',
            'default' => 'valkey_data',
        ],
    ];

    protected $dockerRunTemplate = '-p "${:port}":6379 \
        -v "${:volume}":/data \
        "${:organization}"/"${:image_name}":"${:tag}"';
}
