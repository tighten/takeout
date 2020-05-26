<?php

namespace App\Services;

class MySql extends BaseService
{
    protected $defaultPort = 3306;
    protected $prompts = [
        [
            'shortname' => 'volume',
            'prompt' => 'What is the Docker volume name?',
            'default' => 'mysql_data',
        ],
    ];

    protected $install = '-p {port}:3306 \
        -v {volume}:/data.ms \
        mysql:{tag}';
}
