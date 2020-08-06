<?php

namespace App\Shell;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Stream;

class DockerTags
{
    protected $guzzle;

    public function __construct(Client $guzzle)
    {
        $this->guzzle = $guzzle;
    }

    public function getLatestTag($organization, $imageName): string
    {
        return collect($this->getTags($organization, $imageName))->first(function ($tag) {
            return $tag !== 'latest';
        });
    }

    public function getTags($organization, $imageName): array
    {
        return $this->filterResponseForTags(
            $this->getTagsResponse($organization, $imageName)
        );
    }

    protected function filterResponseForTags(Stream $stream): array
    {
        return collect(json_decode($stream->getContents(), true)['results'])
            ->map(function ($result) {
                return $result['name'];
            })->filter()
            ->toArray();
    }

    protected function getTagsResponse($organization, $imageName): Stream
    {
        return $this->guzzle
            ->get($this->buildTagsUrl($organization, $imageName))
            ->getBody();
    }

    protected function buildTagsUrl($organization, $imageName): string
    {
        return sprintf(
            'https://registry.hub.docker.com/v2/repositories/%s/%s/tags',
            $organization,
            $imageName
        );
    }
}
