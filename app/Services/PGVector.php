<?php

namespace App\Services;

class PGVector extends PostgreSql
{
    protected static $displayName = 'PGVector';

    protected $organization = 'pgvector';
    protected $imageName = 'pgvector';
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
}
