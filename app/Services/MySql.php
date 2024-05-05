<?php

namespace App\Services;

class MySql extends BaseService
{
    protected static $category = Category::DATABASE;

    protected $imageName = 'mysql';

    protected $defaultPort = 3306;

    protected $prompts = [
        [
            'shortname' => 'volume',
            'prompt' => 'What is the Docker volume name?',
            'default' => 'mysql_data',
        ],
        [
            'shortname' => 'root_password',
            'prompt' => 'What will the root password be? (null by default)',
            'default' => '',
        ],
    ];

    protected $dockerRunTemplate = '-p "${:port}":3306 \
        -e MYSQL_ROOT_PASSWORD="${:root_password}" \
        -e MYSQL_ALLOW_EMPTY_PASSWORD="1" \
        -e MYSQL_ROOT_HOST="%" \
        -v "${:volume}":/var/lib/mysql \
        "${:organization}"/"${:image_name}":"${:tag}"';

    protected static $displayName = 'MySQL';
}
