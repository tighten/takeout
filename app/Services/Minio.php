<?php

namespace App\Services;

use App\Shell\MinioDockerTags;

class Minio extends BaseService
{
    protected static $category = Category::STORAGE;

    protected $organization = 'minio';
    protected $imageName = 'minio';
    protected $defaultPort = 9000;
    protected $dockerTagsClass = MinioDockerTags::class;
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
            'shortname' => 'domain',
            'prompt' => 'What domain will Minio be accessible at (optional, to allow dns buckets)?',
            'default' => '',
        ],
        [
            'shortname' => 'root_user',
            'prompt' => 'What will the root user name for Minio be?',
            'default' => 'minioadmin',
        ],
        [
            'shortname' => 'root_password',
            'prompt' => 'What will the root password for Minio be?',
            'default' => 'minioadmin',
        ],
    ];

    protected $dockerRunTemplate = '-p "${:port}":9000 \
        -p "${:console}":9001 \
        -e MINIO_ROOT_USER="${:root_user}" \
        -e MINIO_ROOT_PASSWORD="${:root_password}" \
        -v "${:volume}":/data \
        "${:organization}"/"${:image_name}":"${:tag}" server /data --console-address ":9001"';

    protected function prompts(): void
    {
        parent::prompts();

        if ('' !== $this->promptResponses['domain']) {
            $this->dockerRunTemplate = '-e MINIO_DOMAIN="${:domain}" ' . $this->dockerRunTemplate;
        }
    }
}
