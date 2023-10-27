<?php

namespace App\Shell;

use App\Services\BaseService;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use Psr\Http\Message\StreamInterface;

class DockerTags
{
    protected $guzzle;
    protected $service;

    public function __construct(Client $guzzle, BaseService $service)
    {
        $this->guzzle = $guzzle;
        $this->service = $service;
    }

    public function resolveTag($tag): string
    {
        if ($tag === 'latest') {
            return $this->getLatestTag();
        }

        return $tag;
    }

    public function getLatestTag(): string
    {
        $numericTags = $this->getTags()->reject(function ($tag) {
            return ! is_numeric($tag[0]);
        });

        if ($numericTags->isEmpty()) {
            return 'latest';
        }

        return $numericTags->first();
    }

    public function getTags(): Collection
    {
        $response = json_decode($this->getTagsResponse()->getContents(), true);

        [$numericTags, $alphaTags] = collect($response['results'])
            ->filter($this->onlySupportedImagesFilter())
            ->pluck('name')
            ->partition(function ($tag) {
                return is_numeric($tag[0]);
            });

        $sortedTags = $alphaTags->sortDesc(SORT_NATURAL)
                                ->concat($numericTags->sortDesc(SORT_NATURAL));

        if ($sortedTags->contains('latest')) {
            $sortedTags->splice($sortedTags->search('latest'), 1);
            $sortedTags->prepend('latest');
        }

        return $sortedTags->values()->filter();
    }

    protected function onlySupportedImagesFilter()
    {
        $platform = $this->platform();

        return function ($tag) use ($platform) {
            return collect($tag['images'])
                ->pluck('architecture')
                ->contains($platform);
        };
    }

    protected function platform(): string
    {
        return php_uname('m');
    }

    protected function getTagsResponse(): StreamInterface
    {
        return $this->guzzle
            ->get($this->buildTagsUrl())
            ->getBody();
    }

    protected function buildTagsUrl(): string
    {
        return sprintf(
            $this->tagsUrlTemplate(),
            $this->service->organization(),
            $this->service->imageName()
        );
    }

    protected function tagsUrlTemplate(): string
    {
        return 'https://registry.hub.docker.com/v2/repositories/%s/%s/tags?page_size=1024';
    }
}
