<?php

namespace App\Services;

use App\Shell\Shell;
use App\Shell\Docker;
use App\Shell\Environment;

class EventStoreDB extends BaseService
{
    protected $organization = 'eventstore';
    protected $imageName = 'eventstore';
    protected static $category = Category::DATABASE;
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

    public function __construct(Shell $shell, Environment $environment, Docker $docker)
    {
        parent::__construct($shell, $environment, $docker);

        $this->defaultPrompts = array_map(function ($prompt) {
            if ($prompt['shortname'] === 'tag') {
                $prompt['default'] = '5.0.8-xenial';
            }
            return $prompt;
        }, $this->defaultPrompts);
    }

    // https://discuss.eventstore.com/t/trouble-getting-docker-image-running-on-osx/2589/3
    // For 20.6.0
    // @todo Support 5.0.0
    // @todo Support 20.6.1
    protected $dockerRunTemplate = '-p "${:port}":1113 \
        -p "${:web_port}":2113 \
        -v "${:volume}":/var/lib/eventstore \
        "${:organization}"/"${:image_name}":"${:tag}"';
}
