<?php

namespace Tests\Feature;

use App\Services\MySql;
use App\Shell\DockerTags;
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
        $this->assertTrue(in_array('5.7', $tags));
        $this->assertCount(10, $tags);
    }
}
