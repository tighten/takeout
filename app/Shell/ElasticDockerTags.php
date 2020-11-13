<?php

namespace App\Shell;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Psr\Http\Message\StreamInterface;

class ElasticDockerTags extends DockerTags
{
    public function getTags(): Collection
    {
        return collect(json_decode($this->getTagsResponse(), true)['tags'])
            ->reverse()
            ->filter(function ($tag) {
                return ! Str::contains($tag, 'SNAPSHOT');
            });
    }

    protected function getAuthResponse(): StreamInterface
    {
        return $this->guzzle
            ->get($this->buildAuthUrl())
            ->getBody();
    }

    protected function getTagsResponse(): StreamInterface
    {
        $auth = json_decode($this->getAuthResponse()->getContents(), true);

        return $this->guzzle
            ->get(
                $this->buildTagsUrl(),
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $auth['access_token'],
                    ],
                ]
            )->getBody();
    }

    protected function buildAuthUrl(): string
    {
        return sprintf(
            $this->authUrlTemplate(),
            $this->service->imageName()
        );
    }

    protected function buildTagsUrl(): string
    {
        return sprintf(
            $this->tagsUrlTemplate(),
            $this->service->imageName()
        );
    }

    protected function tagsUrlTemplate(): string
    {
        return 'https://docker.elastic.co/v2/%s/tags/list';
    }

    protected function authUrlTemplate(): string
    {
        return 'https://docker-auth.elastic.co/auth?service=token-service&scope=repository:%s:pull';
    }
}
