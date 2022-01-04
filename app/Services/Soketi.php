<?php

namespace App\Services;

use App\Shell\QuayDockerTags;

class Soketi extends BaseService
{
    protected static $category = Category::SOCKET;

    protected $dockerTagsClass = QuayDockerTags::class;
    protected $organization = 'quay.io';
    protected $imageName = 'soketi/soketi';
    protected $tag = 'latest';
    protected $defaultPort = 6001;
    protected $prompts = [
        [
            'shortname' => 'metrics_port',
            'prompt' => 'Metrics server port',
            'default' => '9601',
        ],
    ];

    protected $dockerRunTemplate = '-p "${:port}":6001 \
        -p "${:metrics_port}":9601 \
        "${:organization}"/"${:image_name}":"${:tag}"';
}
