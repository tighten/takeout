<?php

namespace App\Shell;

use App\Services\BaseService;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Stream;
use Illuminate\Support\Str;
use Psr\Http\Message\StreamInterface;

class MicrosoftDockerTags extends DockerTags
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
        return collect(json_decode($this->getTagsResponse(), true)['tags'])
            ->reverse()
            ->filter(function ($tag) {
                return Str::contains($tag, '-GA-');
            })
            ->first();
    }

    protected function tagsUrlTemplate(): string
    {
        return 'https://%s/v2/%s/tags/list';
    }
}
