<?php

namespace App\Services;

class MariaDb extends BaseService
{
    protected static $category = Category::DATABASE;

    protected $imageName = 'mariadb';
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
            'default' => '',
        ],
    ];

    protected $dockerRunTemplate = '-p "${:port}":3306 \
        -e MYSQL_ROOT_PASSWORD="${:root_password}" \
        -e MYSQL_ALLOW_EMPTY_PASSWORD="${:allow_empty_password}" \
        -v "${:volume}":/var/lib/mysql \
        "${:organization}"/"${:image_name}":"${:tag}"';

    protected static $displayName = 'MariaDB';

    protected function buildParameters(): array
    {
        $parameters = parent::buildParameters();

        $parameters['allow_empty_password'] = $parameters['root_password'] === '' ? 'yes' : 'no';

        return $parameters;
    }
}
