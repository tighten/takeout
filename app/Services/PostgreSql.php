<?php

namespace App\Services;

class PostgreSql extends BaseService
{
    protected $imageName = 'postgres';
    protected $defaultPort = 5432;
    protected $prompts = [
        [
            'shortname' => 'volume',
            'prompt' => 'What is the Docker volume name?',
            'default' => 'postgres_data',
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

    protected static $displayName = 'PostgreSQL';
}
