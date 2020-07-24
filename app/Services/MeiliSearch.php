<?php

namespace App\Services;

class MeiliSearch extends BaseService
{
    protected $organization = 'getmeili';
    protected $imageName = 'meilisearch';
    protected $defaultPort = 7700;
    protected $prompts = [
        [
            'shortname' => 'VOLUME',
            'prompt' => 'What is the Docker volume name?',
            'default' => 'meili_data',
        ],
    ];

    protected $installTemplate = '-p "$PORT":7700 \
        -v "$VOLUME":/data.ms \
        "$ORGANIZATION"/"$IMAGE_NAME":"$TAG"';
}
