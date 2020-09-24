<?php

namespace App\Services;

class Beanstalkd extends BaseService
{
    protected $organization = 'schickling';
    protected $imageName = 'beanstalkd';
    protected static $category = 'cache';
    protected $defaultPort = 11300;

    protected $dockerRunTemplate = '-p "${:port}":11300 \
        "${:organization}"/"${:image_name}":"${:tag}"';
}
