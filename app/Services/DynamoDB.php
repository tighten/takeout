<?php

namespace App\Services;

class DynamoDB extends BaseService
{
    protected static $category = Category::CACHE;

    protected $organization = 'amazon';
    protected $imageName = 'dynamodb-local';
    protected $defaultPort = 8000;
    protected $prompts = [
        [
            'shortname' => 'volume',
            'prompt' => 'What is the Docker volume name?',
            'default' => 'dynamodb_data',
        ],
    ];

    protected $dockerRunTemplate = '-p "${:port}":8000 \
        -u root \
        -v "${:volume}":/dynamodb_local_db \
        "${:organization}"/"${:image_name}":"${:tag}" \
        -jar DynamoDBLocal.jar --sharedDb -dbPath /dynamodb_local_db';
}
