<?php

namespace Tests\Feature;

use App\Services\MySql;
use App\Services\PostgreSql;
use App\Shell\DockerTags;
use GuzzleHttp\Client;
use Mockery as M;
use Tests\TestCase;

class DockerTagsTest extends TestCase
{
    /** @test */
    function it_lists_10_newest_available_tags_for_service()
    {
        $mysql = app(MySql::class);
        $dockerTags = app(DockerTags::class, ['service' => $mysql]);
        $tags = $dockerTags->getTags();

        $this->assertEquals('latest', $tags[0]);
        $this->assertTrue($tags->contains('5.7'));
        $this->assertCount(10, $tags);
    }

    /** @test */
    function it_gets_the_latest_tag_not_named_latest()
    {
        $dockerTags = M::mock(DockerTags::class, [app(Client::class), app(Mysql::class)])->makePartial();
        $dockerTags->shouldReceive('getTags')->andReturn(collect(['latest', 'next latest tag']));

        $this->assertEquals('next latest tag', $dockerTags->getLatestTag());
    }

    /** @test */
    function if_latest_is_the_only_tag_it_returns_latest()
    {
        $dockerTags = M::mock(DockerTags::class, [app(Client::class), app(Mysql::class)])->makePartial();
        $dockerTags->shouldReceive('getTags')->andReturn(collect(['latest']));

        $this->assertEquals('latest', $dockerTags->getLatestTag());
    }

    /** @test */
    function it_sorts_the_versions_naturally()
    {
        $postgres = app(PostgreSql::class);
        $dockerTags = app(DockerTags::class, ['service' => $postgres]);
        $tags = collect($dockerTags->getTags());

        $this->assertEquals('latest', $tags->first());
        $this->assertEquals('9', $tags->last());
        $this->assertCount(10, $tags);
    }
}
