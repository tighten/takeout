<?php

namespace App\Services;

use App\Shell\MongoDockerTags;

class Mongo extends BaseService
{
    protected $imageName = 'mongo';
    protected static $category = Category::DATABASE;
    protected $defaultPort = 27017;
    protected $dockerTagsClass = MongoDockerTags::class;
    protected $prompts = [
        [
            'shortname' => 'volume',
            'prompt' => 'What is the Docker volume name?',
            'default' => 'mongo_data',
        ],
        [
            'shortname' => 'root_user',
            'prompt' => 'What will the root user be?',
            'default' => 'admin',
        ],
        [
            'shortname' => 'root_password',
            'prompt' => 'What will the root password be?',
            'default' => 'password',
        ],
    ];

    protected $dockerRunTemplate = '-p "${:port}":27017 \
        -e MONGO_INITDB_ROOT_USERNAME="${:root_user}" \
        -e MONGO_INITDB_ROOT_PASSWORD="${:root_password}" \
        -v "${:volume}":/data/db \
        "${:organization}"/"${:image_name}":"${:tag}"';
}
