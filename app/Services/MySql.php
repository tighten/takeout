<?php

namespace App\Services;

class MySql extends BaseService
{
    protected $imageName = 'mysql';
    protected $defaultPort = 3306;
    protected $prompts = [
        [
            'shortname' => 'VOLUME',
            'prompt' => 'What is the Docker volume name?',
            'default' => 'mysql_data',
        ],
        [
            'shortname' => 'ROOT_PASSWORD',
            'prompt' => 'What will the root password be?',
            'default' => 'password',
        ],
    ];

    protected $installTemplate = '-p "$PORT":3306 \
        -e MYSQL_ROOT_PASSWORD="$ROOT_PASSWORD" \
        -v "$VOLUME":/var/lib/mysql \
        "$ORGANIZATION"/"$IMAGE_NAME":"$TAG"';
}
