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
    protected $armArchitectures = ['arm64', 'aarch64'];

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

        return collect($response['results'])
            ->when(in_array($platform, $this->armArchitectures, true), $this->armSupportedImagesOnlyFilter())
            ->when(! in_array($platform, $this->armArchitectures, true), $this->nonArmOnlySupportImagesFilter())
            ->pluck('name')
            ->sort(new VersionComparator)
            ->values();
    }

    /**
     * Return a function intended to filter tags, ensuring images that do not support arm architecture are filtered out.
     *
     * @return callable
     */
    protected function armSupportedImagesOnlyFilter()
    {
        return function ($tags) {
            return $tags->filter(function ($tag) {
                $supportedArchs = collect($tag['images'])->pluck('architecture');

                foreach ($this->armArchitectures as $arch) {
                    if ($supportedArchs->contains($arch)) {
                        return true;
                    }
                }

                return false;
            });
        };
    }

    /**
     * Return a function intended to filter tags, that ensures are arm-only images are filtered out.
     *
     * @return callable
     */
    protected function nonArmOnlySupportImagesFilter()
    {
        return function ($tags) {
            return $tags->filter(function ($tag) {
                $supportedArchitectures = collect($tag['images'])
                    ->pluck('architecture')
                    ->unique()
                    ->values();

                // When removing the arm64 option from the list, there should
                // still be other options in the supported architectures
                // so we can consider that the tag is not arm-only.

                return $supportedArchitectures->diff($this->armArchitectures)->count() > 0;
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
