<?php

namespace App\Services;

class Mailhog extends BaseService
{
    protected $organization = 'mailhog';
    protected $imageName = 'mailhog';
    protected $defaultPort = 1025;
    protected $prompts = [
        [
            'shortname' => 'volume',
            'prompt' => 'What is the Docker volume name?',
            'default' => 'mailhog_data',
        ],
        [
            'shortname' => 'web_port',
            'prompt' => 'What will the web port?',
            'default' => '8025',
        ],
    ];

    protected $dockerRunTemplate = '-p "$port":3306 \
        -p "$web_port":8025 \
        -e MYSQL_ROOT_PASSWORD="$root_password" \
        -v "$volume":/var/lib/mysql \
        "$organization"/"$image_name":"$tag"';
}
