<?php

namespace App\Shell;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class MongoDockerTags extends DockerTags
{
    public function getTags(): Collection
    {
        $response = json_decode($this->getTagsResponse()->getContents(), true);
        return collect($response['results'])
            ->pluck('name')
            ->sort(new VersionComparator)
            ->filter(function ($tag) {
                return ! Str::contains($tag, 'windowsservercore');
            })
            ->values();
    }
}
