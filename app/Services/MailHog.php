<?php

namespace App\Services;

class MailHog extends BaseService
{
    protected $organization = 'mailhog';
    protected $imageName = 'mailhog';
    protected $defaultPort = 1025;
    protected $prompts = [
            [
                'shortname' => 'web_port',
                'prompt'    => 'What will the web port?',
                'default'   => '8025',
            ],
            [
                'shortname' => 'volume',
                'prompt'    => 'What is the Docker volume name?',
                'default'   => 'mailhog_data',
            ],
        ];

    protected $dockerRunTemplate = '-p "$port":1025 \
        -p "$web_port":8025 \
        -v "$volume":/var/lib/mysql \
        "$organization"/"$image_name":"$tag"';
}
