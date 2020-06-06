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
        [
            'shortname' => 'root_password',
            'prompt' => 'What will the root password be?',
            'default' => 'password',
        ],
    ];

    protected $install = '-p {port}:3306 \
        -e MYSQL_ROOT_PASSWORD={root_password} \
        -v {volume}:/var/lib/mysql \
        mysql:{tag}';
}
