<?php

namespace App\Services;

class Couchbase extends BaseService
{
    protected static $category = Category::DATABASE;

    protected $imageName = 'couchbase';
    protected $defaultPort = 8091;
    protected $prompts = [
        [
            'shortname' => 'encrypted_port',
            'prompt' => 'Which encrypted port would you like %s to use?',
            'default' => 11210
        ],
        [
            'shortname' => 'volume',
            'prompt' => 'What is the Docker volume name?',
            'default' => 'couchbase_data',
        ],
    ];

    protected $dockerRunTemplate = '-d \
        -p "${:port}":8091 \
        -p "${:encrypted_port}":11210 \
        -v "${:volume}":/opt/couchbase/var \
       "${:image_name}":"${:tag}"';

    protected static $displayName = 'Couchbase';
}
