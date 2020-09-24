<?php

namespace App\Services;

class MariaDb extends BaseService
{
    protected $imageName = 'mariadb';
    protected static $category = 'database';
    protected $defaultPort = 3306;
    protected $prompts = [
        [
            'shortname' => 'volume',
            'prompt' => 'What is the Docker volume name?',
            'default' => 'mariadb_data',
        ],
        [
            'shortname' => 'root_password',
            'prompt' => 'What will the root password be?',
            'default' => 'password',
        ],
    ];

    protected $dockerRunTemplate = '-p "${:port}":3306 \
        -e MYSQL_ROOT_PASSWORD="${:root_password}" \
        -v "${:volume}":/var/lib/mysql \
        "${:organization}"/"${:image_name}":"${:tag}"';

    protected static $displayName = 'MariaDB';
}
