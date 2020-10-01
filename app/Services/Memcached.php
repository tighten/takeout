<?php

namespace App\Services;

class Memcached extends BaseService
{
    protected $imageName = 'memcached';
    protected static $category = Category::CACHE;
    protected $defaultPort = 11211;
    protected $prompts = [];

    protected $dockerRunTemplate = '-p "${:port}":11211 \
            "${:organization}"/"${:image_name}":"${:tag}"';
}
