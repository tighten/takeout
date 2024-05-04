<?php

namespace App\Services;

class MailDev extends BaseService
{
    protected static $category = Category::MAIL;

    protected $organization = 'maildev';
    protected $imageName = 'maildev';
    protected $defaultPort = 1025;
    protected $prompts = [
        [
            'shortname' => 'web_port',
            'prompt' => 'What will the web port be?',
            'default' => '1080',
        ],
    ];

    protected $dockerRunTemplate = '-p "${:port}":1025 \
        -p "${:web_port}":1080 \
        "${:organization}"/"${:image_name}":"${:tag}"';
}
