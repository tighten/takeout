<?php

namespace Tests\Feature;

use App\Services\MySql;
use App\Services\PostgreSql;
use App\Shell\DockerTags;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Mockery as M;
use Tests\Support\FakePlatformDockerTags;
use Tests\TestCase;

class DockerTagsTest extends TestCase
{
    public static function armPlatforms(): array
    {
        return [
            [FakePlatformDockerTags::M1_ARM_PLATFORM],
            [FakePlatformDockerTags::LINUX_ARM_PLATFORM],
        ];
    }

    /** @test */
    function it_gets_the_latest_tag_not_named_latest()
    {
        $dockerTags = M::mock(DockerTags::class, [app(Client::class), app(MySql::class)])->makePartial();
        $dockerTags->shouldReceive('getTags')->andReturn(collect(['latest', 'some named tag', '1.0.0']));

        $this->assertEquals('1.0.0', $dockerTags->getLatestTag());
    }

    /** @test */
    function if_latest_is_the_only_tag_it_returns_latest()
    {
        $dockerTags = M::mock(DockerTags::class, [app(Client::class), app(MySql::class)])->makePartial();
        $dockerTags->shouldReceive('getTags')->andReturn(collect(['latest']));

        $this->assertEquals('latest', $dockerTags->getLatestTag());
    }

    /** @test */
    function it_sorts_the_versions_naturally()
    {
        $postgres = app(PostgreSql::class);
        $dockerTags = app(DockerTags::class, ['service' => $postgres]);
        $tags = collect($dockerTags->getTags());

        $this->assertEquals('latest', $tags->shift());
        $this->assertEquals('16.3', $tags->shift());
    }

    /**
     * @test
     *
     * @dataProvider armPlatforms
     */
    function it_detects_arm_based_images_when_running_on_arm64_based_host($platform)
    {
        $handlerStack = HandlerStack::create($this->mockImagesResponseHandler());
        $client = new Client(['handler' => $handlerStack]);

        $dockerTags = (new FakePlatformDockerTags($client, app(MySql::class)))->withFakePlatform($platform);

        $this->assertEquals('1.0.0-arm64', $dockerTags->getLatestTag());
    }

    /** @test */
    function it_gets_latest_tag_on_intel_platform()
    {
        $handlerStack = HandlerStack::create($this->mockImagesResponseHandler());
        $client = new Client(['handler' => $handlerStack]);

        $dockerTags = (new FakePlatformDockerTags($client, app(MySql::class)))->withFakePlatform(FakePlatformDockerTags::INTEL_ARM_PLATFORM);

        $this->assertEquals('1.0.0', $dockerTags->getLatestTag());
    }

    private function mockImagesResponseHandler()
    {
        return new MockHandler([
            new Response(200, [], json_encode([
                'results' => [
                    [
                        'name' => 'latest',
                        'images' => [
                            ['architecture' => 'x86_64'],
                            ['architecture' => 'amd64'],
                        ],
                    ],
                    [
                        'name' => '1.0.0',
                        'images' => [
                            ['architecture' => 'x86_64'],
                            ['architecture' => 'amd64'],
                        ],
                    ],
                    [
                        'name' => '1.0.0-arm64',
                        'images' => [
                            ['architecture' => 'arm64'],
                            ['architecture' => 'aarch64'],
                        ],
                    ],
                ],
            ])),
        ]);
    }
}
