<?php

namespace App\Services;

class PostGIS extends PostgreSql
{
    protected static $displayName = 'PostGIS';

    protected $organization = 'postgis';
    protected $imageName = 'postgis';
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
}
