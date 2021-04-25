<?php

namespace App\Services;

class TimescaleDB extends BaseService
{
    protected static $category = Category::DATABASE;

    protected static $displayName = 'TimescaleDB';

    protected $organization = 'timescale';
    protected $imageName = 'timescaledb';
    protected $defaultPort = 5432;

    protected $prompts = [
        [
            'shortname' => 'volume',
            'prompt' => 'What is the Docker volume name?',
            'default' => 'timescaledb_data',
        ],
        [
            'shortname' => 'root_password',
            'prompt' => 'What will the password for the `postgres` user be?',
            'default' => 'password',
        ],
        [
            'shortname' => 'tag',
            'prompt' => 'Which tag (version) of %s would you like to use?',
            'default' => '2.2.0-pg13',
        ],
    ];

    protected $dockerRunTemplate = '-p "${:port}":9000 \
        -v ${volume}:/var/lib/postgresql/data \
        "${:organization}"/"${:image_name}":"${:tag}"';
}
