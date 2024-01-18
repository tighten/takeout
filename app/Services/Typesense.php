<?php

namespace App\Services;

class Typesense extends BaseService
{
    protected static $category = Category::SEARCH;

    protected $organization = 'typesense';
    protected $imageName = 'typesense';
    protected $defaultPort = 8108;
    protected $prompts = [
        [
            'shortname' => 'volume',
            'prompt' => 'What is the Docker volume name?',
            'default' => 'typesense_data',
        ],
        [
            'shortname' => 'admin_key',
            'prompt' => 'What will the admin API key be?',
            'default' => 'typesenseadmin',
        ],
    ];

    protected $dockerRunTemplate = '-p "${:port}":8108 \
        -v "${:volume}":/data \
        "${:organization}"/"${:image_name}":"${:tag}" \
        --data-dir /data \
        --api-key="${:admin_key}"';

    protected static $displayName = 'Typesense';
}
