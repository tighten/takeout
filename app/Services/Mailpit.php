<?php

namespace App\Services;

class Mailpit extends BaseService
{
    protected static $category = Category::MAIL;

    protected $organization = 'axllent';
    protected $imageName = 'mailpit';
    protected $defaultPort = 1025;
    protected $prompts = [
        [
            'shortname' => 'web_port',
            'prompt' => 'What will the web port be [8025]?',
            'default' => '8025',
        ],
    ];

    protected $dockerRunTemplate = '-p "${:port}":1025 \
        -p "${:web_port}":8025 \
        "${:organization}"/"${:image_name}":"${:tag}"';

    protected function shellCommand(): string
    {
        return 'sh';
    }
}
