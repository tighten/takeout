<?php

namespace Tests\Feature;

use App\Services\Minio;
use App\Shell\MinioDockerTags;
use GuzzleHttp\Client;
use Mockery as M;
use Tests\Support\MinioDockerTagsFakestream;
use Tests\TestCase;

class MinioDockerTagsTest extends TestCase
{
    /** @test */
    function it_gets_the_latest_tag_not_named_latest()
    {
        $dockerTags = $this->getDockerTags();
        $this->assertEquals('RELEASE.2021-08-17T20-53-08Z', $dockerTags->getLatestTag());
    }

    /** @test */
    function if_latest_is_the_only_tag_it_returns_latest()
    {
        $dockerTags = $this->getDockerTags();
        $dockerTags->shouldReceive('getTags')->andReturn(collect(['latest']));

        $this->assertEquals('latest', $dockerTags->getLatestTag());
    }

    /**
     * @test
     * @note I did a quick Google and I *think* FIPS is an encoding protocol
     *       we probably don't need, but I can be correct via issue/PR if wrong
     *       - @mattstauffer
     * */
    function it_ignores_fips_releases()
    {
        $dockerTags = $this->getDockerTags();

        $dockerTags->getTags()->each(function ($tag) {
            $this->assertStringNotContainsString('fips', $tag);
        });
    }

    /** @test */
    function it_sorts_the_versions_naturally()
    {
        $dockerTags = $this->getDockerTags();
        $tags = $dockerTags->getTags()->values()->toArray();

        $laterReleasePosition = array_search('RELEASE.2021-08-17T20-53-08Z', $tags);
        $earlierReleasePosition = array_search('RELEASE.2021-08-05T22-01-19Z', $tags);

        $this->assertTrue($laterReleasePosition < $earlierReleasePosition);
    }

    /** @test */
    function it_sorts_releases_before_non_releases()
    {
        $dockerTags = $this->getDockerTags();
        $tags = $dockerTags->getTags()->values()->toArray();

        $laterReleasePosition = array_search('RELEASE.2021-08-17T20-53-08Z', $tags);
        $earlierReleasePosition = array_search('edge', $tags);

        $this->assertTrue($laterReleasePosition < $earlierReleasePosition);
    }

    function getDockerTags()
    {
        $minio = app(Minio::class);
        $dockerTags = M::mock(MinioDockerTags::class, [app(Client::class), $minio])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $dockerTags->shouldReceive('getTagsResponse')->andReturn(new MinioDockerTagsFakestream('abc'));

        return $dockerTags;
    }

    protected function setUp(): void
    {
        parent::setUp();

        require_once(base_path('tests/support/MinioDockerTagsFakestream.php'));
    }
}
