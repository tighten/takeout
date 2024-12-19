<?php

namespace App\Services;

class Reverb extends BaseService
{
    protected static $category = Category::SOCKET;

    protected $organization = 'tighten';
    protected $imageName = 'takeout-reverb-server';
    protected $tag = 'latest';
    protected $defaultPort = 6001;
    protected $prompts = [
        [
            'shortname' => 'dashboard_port',
            'prompt' => 'Pulse Dashboard Port',
            'default' => '9601',
        ],
        [
            'shortname' => 'app_id',
            'prompt' => 'Reverb App ID',
            'default' => 'app-id',
        ],
        [
            'shortname' => 'app_key',
            'prompt' => 'Reverb App Key',
            'default' => 'app-key',
        ],
        [
            'shortname' => 'app_secret',
            'prompt' => 'Reverb App Secret',
            'default' => 'app-secret',
        ],
    ];

    protected $dockerRunTemplate = '-p "${:port}":6001 \
        -p "${:dashboard_port}":9601 \
        -e REVERB_APP_KEY="${:app_key}" \
        -e REVERB_APP_SECRET="${:app_secret}" \
        -e REVERB_APP_ID="${:app_id}" \
        "${:organization}"/"${:image_name}":"${:tag}"';

    protected function shellCommand(): string
    {
        return 'sh';
    }
}
