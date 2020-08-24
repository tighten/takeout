<?php

namespace App\Shell;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class MicrosoftDockerTags extends DockerTags
{
    public function getLatestTag(): string
    {
        return $this->getTags()->first();
    }

    public function getTags(): Collection
    {
        return collect(json_decode($this->getTagsResponse(), true)['tags'])
            ->reverse()
            ->filter(function ($tag) {
                return Str::contains($tag, '-GA-');
            });
    }

    protected function tagsUrlTemplate(): string
    {
        return 'https://%s/v2/%s/tags/list';
    }
}
