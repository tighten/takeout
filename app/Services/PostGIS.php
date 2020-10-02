<?php

namespace App\Services;

class PostGIS extends BaseService
{
    protected $organization = 'postgis';
    protected $imageName = 'postgis';
    protected static $category = Category::DATABASE;
    protected $defaultPort = 5432;
    protected $prompts = [
        [
            'shortname' => 'volume',
            'prompt' => 'What is the Docker volume name?',
            'default' => 'postgis_data',
        ],
        [
            'shortname' => 'root_password',
            'prompt' => 'What will the password for the `postgres` user be?',
            'default' => 'password',
        ],
    ];

    protected $dockerRunTemplate = '-p "${:port}":5432 \
        -e POSTGRES_PASSWORD="${:root_password}" \
        -v "${:volume}":/var/lib/postgis/data \
            "${:organization}"/"${:image_name}":"${:tag}"';

    protected static $displayName = 'PostGIS';
}
