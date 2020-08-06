<?php

namespace App\Services;

class Memcached extends BaseService
{
    protected $imageName = 'memcached';
    protected $defaultPort = 11211;
    protected $prompts = [];

    protected $installTemplate = '-p "$port":11212 \
            "$organization"/"$image_name":"$tag"';
}
