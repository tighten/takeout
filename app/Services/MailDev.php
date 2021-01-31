<?php

namespace App\Services;

class MailDev extends BaseService
{
    protected static $category = Category::MAIL;

    protected $organization = 'maildev';
    protected $imageName = 'maildev';
    protected $defaultPort = 1025;
    protected $defaultPrompts = [
        [
            'shortname' => 'port',
            'prompt' => 'Which host port would you like %s to use?',
            // Default is set in the constructor
        ],
        [
            'shortname' => 'tag',
            'prompt' => 'Which tag (version) of %s would you like to use?',
            'default' => '1.1.0',
        ],
    ];
    protected $prompts = [
        [
            'shortname' => 'web_port',
            'prompt' => 'What will the web port be?',
            'default' => '8025',
        ],
    ];

    protected $dockerRunTemplate = '-p "${:port}":25 \
        -p "${:web_port}":80 \
        "${:organization}"/"${:image_name}":"${:tag}"';
}
