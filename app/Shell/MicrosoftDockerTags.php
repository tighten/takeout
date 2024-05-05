<?php

namespace App\Shell;

use Illuminate\Support\Collection;

class MicrosoftDockerTags extends DockerTags
{
    public function getLatestTag(): string
    {
        return $this->getTags()->first();
    }

    public function getTags(): Collection
    {
        return collect(json_decode($this->getTagsResponse(), true)['tags'])
            ->sort(new VersionComparator)
            ->values();
    }

    protected function tagsUrlTemplate(): string
    {
        return 'https://%s/v2/%s/tags/list';
    }
}
