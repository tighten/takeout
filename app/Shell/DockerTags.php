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
            ->when($platform === 'arm64', $this->armSupportedImagesOnlyFilter())
            ->when($platform !== 'arm64', $this->nonArmOnlySupportImagesFilter())
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

    protected function armSupportedImagesOnlyFilter()
    {
        return function ($results) {
            $platform = $this->platform();

            // We need to take into account if the M1 chip is supported by the tag.
            return $results->filter(function ($results) use ($platform) {
                return collect($results['images'])
                    ->pluck('architecture')
                    ->contains($platform);
            });
        };
    }

    protected function nonArmOnlySupportImagesFilter()
    {
        return function ($results) {
            return $results->filter(function ($results) {
                $supportedArchitectures = collect($results['images'])
                    ->pluck('architecture')
                    ->unique()
                    ->values();

                // They should have more architecture options when removing 'arm64'.

                return $supportedArchitectures->diff(['arm64'])->count() > 0;
            });
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
