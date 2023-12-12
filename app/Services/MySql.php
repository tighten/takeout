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
        -e MYSQL_ALLOW_EMPTY_PASSWORD="${:allow_empty_password}" \
        -e MYSQL_ROOT_HOST="%" \
        -v "${:volume}":/var/lib/mysql \
        "${:organization}"/"${:image_name}":"${:tag}" --default-authentication-plugin=mysql_native_password';

    protected static $displayName = 'MySQL';

    protected function buildParameters(): array
    {
        $parameters = parent::buildParameters();

        $parameters['allow_empty_password'] = $parameters['root_password'] === '' ? '1' : '0';

        return $parameters;
    }
}
