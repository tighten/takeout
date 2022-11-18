<?php

namespace Tests\Support;

class FakeDockerTags
{
    public function resolveTag($tag)
    {
        return $tag;
    }
}
