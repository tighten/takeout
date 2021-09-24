<?php

namespace App\Services;

class OpenSearch extends BaseService
{
    protected static $category = Category::SEARCH;

    protected $organization = 'opensearchproject';
    protected $imageName = 'opensearchproject/opensearch';
    protected $defaultPort = 9200;
    protected $prompts = [
        [
            'shortname' => 'volume',
            'prompt' => 'What is the Docker volume name?',
            'default' => 'opensearch_data',
        ],
        [
            'shortname' => 'analyzer_port',
            'prompt' => 'Which host port would you like to be used by the performance analyzer?',
            'default' => 9600,
        ],
    ];

    protected $dockerRunTemplate = '-p "${:port}":9200 \
        -p ${:analyzer_port}:9600
        -e "discovery.type=single-node"  \
        -v "${:volume}":/usr/share/opensearch/data \
         "${:organization}"/"${:image_name}":"${:tag}"';

    protected static $displayName = 'OpenSearch';
}
