<?php

namespace App\Services;

class Neo4j extends BaseService
{
    protected static $category = Category::DATABASE;

    protected $imageName = 'neo4j';
    protected $defaultPort = 7474;
    protected $prompts = [
        [
            'shortname' => 'volume',
            'prompt' => 'What is the Docker volume name?',
            'default' => 'neo4j_data',
        ],
        [
            'shortname' => 'bolt_access_port',
            'prompt' => 'What will the Bolt access port be?',
            'default' => '7687',
        ],
    ];

    protected $dockerRunTemplate = '-p "${:port}":7474 \
        -p "${:bolt_access_port}":7687 \
        -e NEO4J_AUTH=none \
        -e NEO4J_ACCEPT_LICENSE_AGREEMENT=yes \
        -v "${:volume}":/data \
        "${:organization}"/"${:image_name}":"${:tag}"';

    protected static $displayName = 'Neo4j';
}
