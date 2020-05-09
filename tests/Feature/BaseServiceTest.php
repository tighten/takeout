<?php

namespace Tests\Feature;

use App\Services\MeiliSearch;
use Tests\TestCase;

class BaseServiceTest extends TestCase
{
    /** @test */
    function it_generates_shortname()
    {
        $meilisearch = new MeiliSearch;
        $this->assertEquals('meilisearch', $meilisearch->shortName());
    }
}
