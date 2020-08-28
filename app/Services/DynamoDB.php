<?php

namespace App\Services;

class DynamoDB extends BaseService
{
    protected $organization = 'cnadiminti';
    protected $imageName = 'dynamodb-local';
    protected $defaultPort = 8000;
    protected $prompts = [
        [
            'shortname' => 'volume',
            'prompt' => 'What is the Docker volume name?',
            'default' => 'dynamodb_data',
        ],
    ];

    protected $dockerRunTemplate = '-p "$port":8000 \
        -v "$volume":/dynamodb_local_db \
        "$organization"/"$image_name":"$tag"';
}
