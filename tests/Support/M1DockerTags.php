<?php

namespace Tests\Support;

use App\Shell\DockerTags;

class M1DockerTags extends DockerTags
{
    protected function platform(): string
    {
        return 'arm64';
    }
}
