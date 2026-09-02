<?php

namespace App\Services;

use App\Shell\Docker;
use App\Shell\Environment;
use App\Shell\Shell;

class Timescale extends PostgreSql
{
    protected static $displayName = 'Timescale';

    protected $organization = 'timescale';
    protected $imageName = 'timescaledb';
    protected $tag = 'latest-pg16';
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
