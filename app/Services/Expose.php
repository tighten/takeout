<?php

namespace App\Services;

class Expose extends BaseService
{
    protected static $category = Category::TOOLS;

    protected $organization = 'beyondcodegmbh';
    protected $imageName = 'expose-server';
    protected $defaultPort = 8080;
    protected $prompts = [
        [
            'shortname' => 'domain',
            'prompt' => 'What is the domain?',
            'default' => 'example.com',
        ],
        [
            'shortname' => 'volume',
            'prompt' => 'What is the Docker volume name?',
            'default' => 'expose_data',
        ],
        [
            'shortname' => 'username',
            'prompt' => 'What is the username?',
            'default' => 'username',
        ],
        [
            'shortname' => 'password',
            'prompt' => 'What is the password?',
            'default' => 'password',
        ],
    ];


    protected $dockerRunTemplate = '-p "${:port}":8080 \
        -v "${:volume}":/root/.expose \
        -e username="${:username}" \
        -e password="${:password}" \
        -e domain="${:domain}" \
        "${:organization}"/"${:image_name}":"${:tag}"';
}
