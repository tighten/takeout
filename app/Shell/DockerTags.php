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

        $platform = $this->platform();

        [$numericTags, $alphaTags] = collect($response['results'])
            ->when($this->isArm($platform), $this->onlyArmImagesFilter())
            ->when(! $this->isArm($platform), $this->onlyNonArmImagesFilter())
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

    protected function onlyArmImagesFilter()
    {
        return function ($tags) {
            return $tags->filter(function ($tag) {
                return collect($tag['images'])
                    ->pluck('architecture')
                    ->first(function (string $platform) {
                        return $this->isArm($platform);
                    });
            });
        };
    }

    protected function onlyNonArmImagesFilter()
    {
        return function ($tags) {
            return $tags->filter(function ($tag) {
                return collect($tag['images'])
                    ->pluck('architecture')
                    ->first(function (string $platform) {
                        return ! $this->isArm($platform);
                    });
            });
        };
    }

    protected function platform(): string
    {
        return php_uname('m');
    }

    protected function isArm(string $platform): bool
    {
        return in_array($platform, ['arm64', 'aarch64']);
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
