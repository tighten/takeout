<?php

namespace App\Services;

class Redis extends BaseService
{
    protected $imageName = 'redis';
    protected $defaultPort = 6379;
    protected $prompts = [
        [
            'shortname' => 'volume',
            'prompt' => 'What is the Docker volume name?',
            'default' => 'redis_data',
        ],
    ];

    protected $installTemplate = '-p "$port":6379 \
        -v "$volume":/data \
        "$organization"/"$image_name":"$tag"';
}
