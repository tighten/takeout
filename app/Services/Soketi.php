<?php

namespace App\Services;

use App\Shell\Docker;
use App\Shell\Environment;
use App\Shell\QuayDockerTags;
use App\Shell\Shell;

class Soketi extends BaseService
{
    protected static $category = Category::SOCKET;

    protected $dockerTagsClass = QuayDockerTags::class;
    protected $organization = 'quay.io';
    protected $imageName = 'soketi/soketi';
    protected $tag = 'latest-16-alpine';
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
        -e SOKETI_METRICS_ENABLED=1 \
        "${:organization}"/"${:image_name}":"${:tag}"';

    public function __construct(Shell $shell, Environment $environment, Docker $docker)
    {
        parent::__construct($shell, $environment, $docker);

        $this->defaultPrompts = array_map(function ($prompt) {
            if ($prompt['shortname'] === 'tag') {
                $prompt['default'] = $this->tag;
            }

            return $prompt;
        }, $this->defaultPrompts);
    }
}
