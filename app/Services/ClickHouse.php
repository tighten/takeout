<?php

namespace App\Services;

class ClickHouse extends BaseService
{
    protected static $category = Category::DATABASE;

    protected $organization = 'yandex';
    protected $imageName = 'clickhouse-server';
    protected $defaultPort = 9000;
    protected $prompts = [
        [
            'shortname' => 'http_port',
            'prompt' => 'Which http host port would you like to use?',
            'default' => '8123',
        ],
        [
            'shortname' => 'volume',
            'prompt' => 'What is the Docker volume name?',
            'default' => 'clickhouse_data',
        ],
    ];

    protected $dockerRunTemplate = '-p "${:port}":9000 \
        -p "${:http_port}":8123 \
        --ulimit nofile=262144:262144 \
        --volume=${volume}:/var/lib/clickhouse \
        "${:organization}"/"${:image_name}":"${:tag}"';
}
