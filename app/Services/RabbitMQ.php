<?php

namespace App\Services;

class RabbitMQ extends BaseService
{
    protected static $category = Category::CACHE;

    protected $imageName = 'rabbitmq';
    protected $defaultPort = 5672;
    protected $defaultPrompts = [
        [
            'shortname' => 'port',
            'prompt' => 'Which host port would you like %s to use?',
            // Default is set in the constructor
        ],
        [
            'shortname' => 'tag',
            'prompt' => 'Which tag (version) of %s would you like to use?',
            'default' => 'management',
        ],
    ];
    protected $prompts = [
        [
            'shortname' => 'hostname',
            'prompt' => 'Which hostname would you like %s to use?',
            'default' => 'takeout'
        ],
        [
            'shortname' => 'mgmt_port',
            'prompt' => 'Which management port would you like %s to use?',
            'default' => 15672
        ],
    ];

    protected $dockerRunTemplate = '-h "${:hostname}" \
        -p "${:port}":5672 \
        -p "${:mgmt_port}":15672 \
        "${:organization}"/"${:image_name}":"${:tag}"';
}
