<?php

namespace Tests\Support;

use App\Shell\DockerTags;

class IntelDockerTags extends DockerTags
{
    protected function platform(): string
    {
        return 'x86_64';
    }
}
