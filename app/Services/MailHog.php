<?php

namespace App\Services;

class MailHog extends BaseService
{
    protected $organization = 'mailhog';
    protected $imageName = 'mailhog';
    protected static $category = 'mail';
    protected $defaultPort = 1025;
    protected $prompts = [
        [
            'shortname' => 'web_port',
            'prompt' => 'What will the web port be?',
            'default' => '8025',
        ]
    ];

    protected $dockerRunTemplate = '-p "${:port}":1025 \
        -p "${:web_port}":8025 \
        "${:organization}"/"${:image_name}":"${:tag}"';
}
