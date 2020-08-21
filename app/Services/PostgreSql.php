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
            'prompt' => 'What will the root password be?',
            'default' => 'password',
        ],
    ];

    protected $dockerRunTemplate = '-p "$port":3306 \
        -e POSTGRES_PASSWORD="$root_password" \
        -v "$volume":/var/lib/postgresql/data \
            "$organization"/"$image_name":"$tag"';
}
