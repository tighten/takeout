<?php

namespace Tests\Feature;

use App\Services\Buggregator;
use App\Shell\DockerTags;
use App\Shell\GitHubDockerTags;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Mockery as M;
use RuntimeException;
use Tests\TestCase;

class GitHubTagsTest extends TestCase
{
    /** @test */
    function it_fetches_latest_tag()
    {
        $handlerStack = HandlerStack::create($this->mockImagesResponseHandler());
        $client = new Client(['handler' => $handlerStack]);

        /** @var DockerTags $dockerTags */
        $dockerTags = M::mock(GitHubDockerTags::class, [$client, app(Buggregator::class)])->makePartial();

        $this->assertEquals('latest', $dockerTags->getLatestTag());
    }

    /** @test */
    function it_throws_exception_when_token_request_fails()
    {
        $handlerStack = HandlerStack::create($this->mockImagesResponseHandler(false));
        $client = new Client(['handler' => $handlerStack]);

        /** @var DockerTags $dockerTags */
        $dockerTags = M::mock(GitHubDockerTags::class, [$client, app(Buggregator::class)])->makePartial();

        $this->expectException(RuntimeException::class);

        $dockerTags->getLatestTag();
    }

    private function mockImagesResponseHandler($tokenWorks = true)
    {
        return new MockHandler([
            new Response($tokenWorks ? 200 : 400, [], json_encode([
                'token' => 'fake-token',
            ])),
            new Response(200, [], json_encode([
                'tags' => [
                    'latest',
                    '1.0.0',
                    '1.0.0.rc-1',
                ],
            ])),
        ]);
    }
}
