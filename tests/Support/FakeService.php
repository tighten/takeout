<?php

namespace Tests\Support;

use App\Services\BaseService;
use App\Services\Category;

class FakeService extends BaseService
{
    protected static $category = Category::CACHE;
    protected $dockerTagsClass = FakeDockerTags::class;

    protected $organization = 'tighten';
    protected $imageName = '_test_image';
    protected $defaultPort = 12345;

    protected $dockerRunTemplate = '"${:organization}"/"${:image_name}":"${:tag}"';
}
