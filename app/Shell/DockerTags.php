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

    public function getLatestTag(): string
    {
        return $this->getTags()->first(function ($tag) {
            return $tag !== 'latest';
        });
    }

    public function getTags(): Collection
    {
        $response = json_decode($this->getTagsResponse()->getContents(), true);

        return collect($response['results'])->map->name->filter();
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
        return 'https://registry.hub.docker.com/v2/repositories/%s/%s/tags';
    }
}
