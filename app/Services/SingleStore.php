<?php

namespace App\Services;

class SingleStore extends BaseService
{
    protected static $category = Category::DATABASE;

    protected $organization = 'singlestore';
    protected $imageName = 'cluster-in-a-box';
    protected $defaultPort = 3306;
    protected $prompts = [
        [
            'shortname' => 'http_port',
            'prompt' => 'Which http host port would you like to use?',
            'default' => '8080',
        ],
        [
            'shortname' => 'volume',
            'prompt' => 'What is the docker volume name?',
            'default' => 'singlestore_data',
        ],
        [
            'shortname' => 'license',
            'prompt' => 'What is your license key?',
            'default' => '',
        ],
        [
            'shortname' => 'root_password',
            'prompt' => 'What will the root password be?',
            'default' => 'password',
        ],
    ];

    protected $dockerRunTemplate = '-p "${:port}":3306 \
        -p "${:http_port}":8080 \
        -e LICENSE_KEY="${:license}" \
        -e ROOT_PASSWORD="${:root_password}" \
        --volume=${volume}:/var/lib/memsql \
        "${:organization}"/"${:image_name}":"${:tag}"';
}
