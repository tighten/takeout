<?php

namespace App\Shell;

use Illuminate\Support\Collection;

class QuayDockerTags extends DockerTags
{
    public function getLatestTag(): string
    {
        return $this->getTags()->first();
    }

    /**
     * DockerTags sorts by numeric first, then alpha. In Minio, that would be
     * sorting by tags starting with "RELEASE." first, and then the rest.
     *
     * And, like DockerTags, we throw 'latest' at the top.
     */
    public function getTags(): Collection
    {
        return collect(json_decode($this->getTagsResponse()->getContents(), true)['tags'])
            ->map(function ($release) {
                return $release['name'];
            })
        ;
    }

    protected function tagsUrlTemplate(): string
    {
        return 'https://%s/api/v1/repository/%s/tag/?limit=1024&page=1&onlyActiveTags=true';
    }
}
