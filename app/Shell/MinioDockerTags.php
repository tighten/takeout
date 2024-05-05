<?php

namespace App\Shell;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class MinioDockerTags extends DockerTags
{
    public function getLatestTag(): string
    {
        $releaseTags = $this->getTags()->filter(function ($tag) {
            return Str::contains($tag, 'RELEASE.');
        });

        if ($releaseTags->isEmpty()) {
            return 'latest';
        }

        return $releaseTags->first();
    }

    /**
     * DockerTags sorts by numeric first, then alpha. In Minio, that would be
     * sorting by tags starting with "RELEASE." first, and then the rest.
     *
     * And, like DockerTags, we throw 'latest' at the top.
     */
    public function getTags(): Collection
    {
        $response = json_decode($this->getTagsResponse()->getContents(), true);

        return collect($response['results'])
            ->pluck('name')
            ->reject(function ($tag) {
                return Str::endsWith($tag, 'fips');
            })
            ->sort(new VersionComparator)
            ->values();
    }
}
