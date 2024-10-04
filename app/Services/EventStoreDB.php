<?php

namespace App\Services;

class EventStoreDB extends BaseService
{
    protected static $category = Category::DATABASE;

    protected $organization = 'eventstore';
    protected $imageName = 'eventstore';
    protected $defaultPort = 1113;
    protected $prompts = [
        [
            'shortname' => 'web_port',
            'prompt' => 'What will the web port be?',
            'default' => '2113',
        ],
        [
            'shortname' => 'volume',
            'prompt' => 'What is the Docker volume name?',
            'default' => 'eventstore_data',
        ],
    ];

    protected $dockerRunTemplate = '-p "${:port}":1113 \
        -p "${:web_port}":2113 \
        -v "${:volume}":/var/lib/eventstore \
        "${:organization}"/"${:image_name}":latest \
        --insecure --run-projections=All';
}
