<?php

namespace App\Services;

use App\Shell\GitHubDockerTags;

class Buggregator extends BaseService
{
    protected static $category = Category::TOOLS;

    protected $dockerTagsClass = GitHubDockerTags::class;
    protected $organization = 'ghcr.io';
    protected $imageName = 'buggregator/server';
    protected $tag = 'latest';
    protected $defaultPort = 8000;
    protected $prompts = [
        [
            'shortname' => 'smtp_port',
            'prompt' => 'What is the SMTP port?',
            'default' => '1025',
        ],
        [
            'shortname' => 'var_dumper_port',
            'prompt' => 'What is the VarDumper server port?',
            'default' => '9912',
        ],
        [
            'shortname' => 'monolog_port',
            'prompt' => 'What is the Monolog port?',
            'default' => '9913',
        ],
    ];

    protected $dockerRunTemplate = '-p "${:port}":8000 \
        -p "${:smtp_port}":1025 \
        -p "${:var_dumper_port}":9912 \
        -p "${:monolog_port}":9913 \
        --network-alias dump-server \
        "${:organization}"/"${:image_name}":"${:tag}"';
}
