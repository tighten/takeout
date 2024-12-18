<?php

namespace App\Services;

use App\Shell\Docker;
use App\Shell\Environment;
use App\Shell\Shell;

class Timescale extends BaseService
{
    protected static $category = Category::DATABASE;

    protected $organization = 'timescale';
    protected $imageName = 'timescaledb';
    protected $tag = 'latest-pg16';
    protected $defaultPort = 5432;
    protected $prompts = [
        [
            'shortname' => 'volume',
            'prompt' => 'What is the Docker volume name?',
            'default' => 'timescale_data',
        ],
        [
            'shortname' => 'root_password',
            'prompt' => 'What will the password for the `postgres` user be?',
            'default' => 'password',
        ],
    ];

    protected $dockerRunTemplate = '-p "${:port}":5432 \
        -e POSTGRES_PASSWORD="${:root_password}" \
        -v "${:volume}":/var/lib/postgresql/data \
            "${:organization}"/"${:image_name}":"${:tag}"';

    protected static $displayName = 'Timescale';

    public function __construct(Shell $shell, Environment $environment, Docker $docker)
    {
        parent::__construct($shell, $environment, $docker);

        $this->defaultPrompts = array_map(function ($prompt) {
            if ($prompt['shortname'] === 'tag') {
                $prompt['default'] = $this->tag;
            }

            return $prompt;
        }, $this->defaultPrompts);
    }
}
