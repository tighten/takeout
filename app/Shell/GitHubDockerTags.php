<?php

namespace App\Shell;

use Illuminate\Support\Collection;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

class GitHubDockerTags extends DockerTags
{
    public function getTags(): Collection
    {
        return collect(json_decode($this->getTagsResponse(), true)['tags'])
            ->sort(new VersionComparator)
            ->values();
    }

    public function getLatestTag(): string
    {
        return $this->getTags()->first();
    }

    protected function getTagsResponse(): StreamInterface
    {
        $token = $this->getToken();

        return $this->guzzle
            ->get($this->buildTagsUrl(), [
                'headers' => [
                    'Authorization' => "Bearer {$token}",
                ],
            ])
            ->getBody();
    }

    protected function getToken(): string
    {
        $image = $this->service->imageName();

        $response = $this->guzzle->get('https://ghcr.io/token?' . http_build_query([
            'scope' => "repository:{$image}:pull",
        ]), [
            'http_errors' => false,
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new RuntimeException("Something went wrong getting the Token from GitHub's registry.");
        }

        return json_decode($response->getBody(), true)['token'];
    }

    protected function tagsUrlTemplate(): string
    {
        return 'https://%s/v2/%s/tags/list';
    }
}
