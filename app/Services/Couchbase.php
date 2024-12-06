<?php

namespace App\Services;

class Couchbase extends BaseService
{
    protected static $category = Category::DATABASE;

    protected $imageName = 'couchbase';
    protected $defaultPrompts = [
        [
            'shortname' => 'ports',
            'prompt' => 'Which port(s) would you like %s to use? (Must be 6 ports)',
            'default' => '8091-8096',
        ],
        [
            'shortname' => 'tag',
            'prompt' => 'Which tag (version) of %s would you like to use?',
            'default' => 'latest',
        ],
        [
            'shortname' => 'encrypted_ports',
            'prompt' => 'Which encrypted port(s) would you like %s to use? (Must be 2 ports)',
            'default' => '11210-11211',
        ],
        [
            'shortname' => 'volume',
            'prompt' => 'What is the Docker volume name?',
            'default' => 'couchbase_data',
        ],
    ];

    protected $dockerRunTemplate = '-d \
        -p "${:ports}":8091-8096 \
        -p "${:encrypted_ports}":11210-11211 \
        -v "${:volume}":/opt/couchbase/var \
       "${:image_name}":"${:tag}"';

    protected static $displayName = 'Couchbase';
}
