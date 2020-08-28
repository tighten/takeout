<?php

namespace App\Services;

class Minio extends BaseService
{
    protected $imageName = 'minio';
    protected $defaultPort = 9000;
    protected $prompts = [
        [
            'shortname' => 'volume',
            'prompt' => 'What is the Docker volume name?',
            'default' => 'minio_data',
        ],
        [
            'shortname' => 'access_key',
            'prompt' => 'What will the access key for Minio be?',
            'default' => 'minio',
        ],
        [
            'shortname' => 'secret_key',
            'prompt' => 'What will the secret key for Minio be?',
            'default' => 'minio',
        ],
    ];

    protected $dockerRunTemplate = '-p "$port":9000 \
        -e MINIO_ACCESS_KEY=$access_key
        -e MINIO_SECRET_KEY=$secret_key
        -v "$volume":/data \
        "$organization"/"$image_name":"$tag"';
}
