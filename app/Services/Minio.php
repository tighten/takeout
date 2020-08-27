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
    ];

    protected $dockerRunTemplate = '-p "$port":9000 \
        -e MINIO_ACCESS_KEY=minio
        -e MINIO_SECRET_KEY=minio
        -v "$volume":/data \
        "$organization"/"$image_name":"$tag"';
}
