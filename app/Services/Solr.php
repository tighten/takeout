<?php

namespace App\Services;

class Solr extends BaseService
{
    protected static $category = Category::SEARCH;

    protected $imageName = 'solr';
    protected $defaultPort = 8983;
    protected $prompts = [
        [
            'shortname' => 'volume',
            'prompt' => 'What is the Docker volume name?',
            'default' => 'solr_data',
        ],
    ];

    protected $dockerRunTemplate = '-p "${:port}":8983 \
        -v "${:volume}":/var/solr \
            "${:organization}"/"${:image_name}":"${:tag}"';
}
