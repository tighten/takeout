<?php

namespace App\Services;

class Minio extends BaseService
{
    protected static $category = Category::STORAGE;

    protected $organization = 'minio';
    protected $imageName = 'minio';
    protected $defaultPort = 9000;
    protected $prompts = [
        [
            'shortname' => 'volume',
            'prompt' => 'What is the Docker volume name?',
            'default' => 'minio_data',
        ],
        [
            'shortname' => 'console',
            'prompt' => 'Which host port would you like to be used by Minio Console?',
            'default' => 9001,
        ],
        [
            'shortname' => 'access_key',
            'prompt' => 'What will the access key for Minio be?',
            'default' => 'minioadmin',
        ],
        [
            'shortname' => 'secret_key',
            'prompt' => 'What will the secret key for Minio be?',
            'default' => 'minioadmin',
        ],
    ];

    protected $dockerRunTemplate = '-p "${:port}":9000 \
        -p "${:console}":9001 \
        -e MINIO_ACCESS_KEY="${:access_key}" \
        -e MINIO_SECRET_KEY="${:secret_key}" \
        -v "${:volume}":/data \
        "${:organization}"/"${:image_name}":"${:tag}" server /data --console-address ":9001"';
}
