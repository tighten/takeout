<?php

namespace App\Services;

class PGVector extends BaseService
{
    protected static $displayName = 'PGVector';
    protected static $category = Category::DATABASE;

    protected $organization = 'pgvector';
    protected $imageName = 'pgvector';
    protected $defaultPort = 5432;
    protected $prompts = [
        [
            'shortname' => 'volume',
            'prompt' => 'What is the Docker volume name?',
            'default' => 'pgvector_data',
        ],
        [
            'shortname' => 'root_password',
            'prompt' => 'What will the password for the `postgres` user be?',
            'default' => 'password',
        ],
    ];

    protected $dockerRunTemplate = '-p "${:port}":5432 \
        -e POSTGRES_PASSWORD="${:root_password}" \
        -v "${:volume}":/var/lib/postgresql/data \
            "${:organization}"/"${:image_name}":"${:tag}"';
}
