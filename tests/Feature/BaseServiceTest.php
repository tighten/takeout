<?php

namespace Tests\Feature;

use App\Services\MeiliSearch;
use App\Services\MySql;
use Tests\TestCase;

class BaseServiceTest extends TestCase
{
    /** @test */
    function it_generates_shortname()
    {
        $meilisearch = app(MeiliSearch::class);
        $this->assertEquals('meilisearch', $meilisearch->shortName());
    }

    /** @test */
    function it_lists_10_newest_available_tags_for_service()
    {
        $mysql = app(MySql::class);
        $tags = $mysql->getTags();
        $this->assertEquals('latest', $tags[0]);
        $this->assertTrue(in_array('5.7', $tags));
        $this->assertCount(10, $tags);
    }
}
