<?php

namespace App\Services;

class OpenSearch extends BaseService
{
    protected static $category = Category::SEARCH;

    protected $organization = 'opensearchproject';
    protected $imageName = 'opensearch';
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
        [
            'shortname' => 'disable_security',
            'prompt' => 'Disable security plugin (true or false)?',
            'default' => 'true',
        ],
        [
            'shortname' => 'password',
            'prompt' => 'What is the initial admin password?',
            'default' => 'admin',
        ],
    ];

    protected $dockerRunTemplate = '-p "${:port}":9200 \
        -p "${:analyzer_port}":9600 \
        -e DISABLE_SECURITY_PLUGIN="${:disable_security}"  \
        -e OPENSEARCH_INITIAL_ADMIN_PASSWORD="${:password}" \
        -e "discovery.type=single-node"  \
        -v "${:volume}":/usr/share/opensearch/data \
        "${:organization}"/"${:image_name}":"${:tag}"';

    protected static $displayName = 'OpenSearch';
}
