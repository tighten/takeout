<?php

namespace App\Services;

class RustFS extends BaseService
{
    protected static $category = Category::STORAGE;
    protected static $displayName = 'RustFS';

    protected $organization = 'rustfs';
    protected $imageName = 'rustfs';
    protected $defaultPort = 9000;
    protected $prompts = [
        [
            'shortname' => 'volume',
            'prompt' => 'What is the Docker volume name?',
            'default' => 'rustfs_data',
        ],
        [
            'shortname' => 'console_port',
            'prompt' => 'Which host port would you like RustFS Console to use?',
            'default' => 9001,
        ],
        [
            'shortname' => 'access_key',
            'prompt' => 'What will the RustFS access key be?',
            'default' => 'rustfsadmin',
        ],
        [
            'shortname' => 'secret_key',
            'prompt' => 'What will the RustFS secret key be?',
            'default' => 'rustfsadmin',
        ],
    ];

    protected $dockerRunTemplate = '-p "${:port}":9000 \
        -p "${:console_port}":9001 \
        -e RUSTFS_ACCESS_KEY="${:access_key}" \
        -e RUSTFS_SECRET_KEY="${:secret_key}" \
        -e RUSTFS_ADDRESS=":9000" \
        -e RUSTFS_CONSOLE_ADDRESS=":9001" \
        -e RUSTFS_CONSOLE_ENABLE=true \
        -v "${:volume}":/data \
        "${:organization}"/"${:image_name}":"${:tag}" /data';
}
