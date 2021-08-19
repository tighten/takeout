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
        $tags = collect($response['results'])->map->name->reject(function ($tag) {
            return Str::endsWith($tag, 'fips');
        });

        [$releaseTags, $otherTags] = $tags
            ->partition(function ($tag) {
                return Str::startsWith($tag, 'RELEASE.');
            });

        $sortedTags = $releaseTags->sortDesc(SORT_NATURAL)
            ->concat($otherTags->sortDesc(SORT_NATURAL));

        if ($sortedTags->contains('latest')) {
            $sortedTags->splice($sortedTags->search('latest'), 1);
            $sortedTags->prepend('latest');
        }

        return $sortedTags;
    }
}
