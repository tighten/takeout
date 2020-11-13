<?php

namespace App\Services;

class Sqs extends BaseService
{
    protected static $category = Category::CACHE;

    protected $organization = 'roribio16';
    protected $imageName = 'alpine-sqs';
    protected $defaultPort = 9324;
    protected $prompts = [
        [
            'shortname' => 'volume',
            'prompt' => 'What is the Docker volume name?',
            'default' => 'sqs_data',
        ],
        [
            'shortname' => 'management_port',
            'prompt' => 'What will the management port be?',
            'default' => '9325',
        ],
    ];

    protected $dockerRunTemplate = '-p "${:port}":9324 \
        -p "${:management_port}":9325 \
        -v "${:volume}":/data \
        "${:organization}"/"${:image_name}":"${:tag}"';
}
