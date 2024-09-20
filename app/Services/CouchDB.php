<?php

namespace App\Services;

class CouchDB extends BaseService
{
    protected static $category = Category::DATABASE;

    protected $imageName = 'couchdb';

    protected $defaultPort = 5984;

    protected $prompts = [
        [
            'shortname' => 'volume',
            'prompt' => 'What is the Docker volume name?',
            'default' => 'couchdb_data',
        ],
        [
            'shortname' => 'user',
            'prompt' => 'What is the CouchDB User?',
            'default' => 'couchdb',
        ],
        [
            'shortname' => 'password',
            'prompt' => 'What is the CouchDB Password?',
            'default' => 'password',
        ],
    ];

    protected $dockerRunTemplate = '-p "${:port}":5984 \
        -v "${:volume}":/opt/couchdb/data \
        -e COUCHDB_USER="${:user}" \
        -e COUCHDB_PASSWORD="${:password}" \
        "${:organization}"/"${:image_name}":"${:tag}"';

    protected static $displayName = 'CouchDB';
}
