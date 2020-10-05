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

    /**
     * The following only supports version 5.X.
     *
     * 20.6.0 requires various env variables to be set and forces HTTPS which is a pain in local dev.
     * 20.6.1 can disable HTTPS but there's no auth which means most features are disabled.
     */
    protected $dockerRunTemplate = '-p "${:port}":1113 \
        -p "${:web_port}":2113 \
        -v "${:volume}":/var/lib/eventstore \
        "${:organization}"/"${:image_name}":"${:tag}"';
}
