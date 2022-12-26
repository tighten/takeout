<?php

namespace App\Services;

use App\Shell\ElasticDockerTags;

class ElasticSearch extends BaseService
{
    protected static $category = Category::SEARCH;

    protected $organization = 'docker.elastic.co';
    protected $imageName = 'elasticsearch/elasticsearch';
    protected $dockerTagsClass = ElasticDockerTags::class;
    protected $defaultPort = 9200;
    protected $prompts = [
        [
            'shortname' => 'volume',
            'prompt' => 'What is the Docker volume name?',
            'default' => 'elastic_data',
        ],
        [
            'shortname' => 'enable_security',
            'prompt' => 'Enable security (true or false)?',
            'default' => 'true',
        ],
    ];

    protected $dockerRunTemplate = '-p "${:port}":9200 \
        -e "discovery.type=single-node" \
        -e xpack.security.enabled="${:enable_security}" \
        -v "${:volume}":/usr/share/elasticsearch/data \
         "${:organization}"/"${:image_name}":"${:tag}"';

    protected static $displayName = 'Elasticsearch';
}
