<?php

namespace Tests\Feature;

use App\Services\MsSql;
use App\Shell\MicrosoftDockerTags;
use GuzzleHttp\Client;
use Mockery as M;
use Tests\TestCase;

class MicrosoftDockerTagsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        require_once(base_path('tests/support/MicrosoftDockerTagsFakestream.php'));
    }

    /** @test */
    function it_gets_a_general_availability_release()
    {
        $dockerTags = app(MicrosoftDockerTags::class, ['service' => app(MsSql::class)]);
        $this->assertStringContainsString('-GA-', $dockerTags->getLatestTag());
    }

    /** @test */
    function it_ignores_tags_without_ga_string()
    {
        $mssql = app(MsSql::class);
        $dockerTags = M::mock(MicrosoftDockerTags::class, [app(Client::class), $mssql])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $dockerTags->shouldReceive('getTagsResponse')->andReturn(new MicrosoftDockerTagsFakestream('abc'));

        $tags = $dockerTags->getTags();

        $this->assertEquals(2, $tags->count());
        $this->assertStringContainsString('-GA-', $tags->first());
    }

    /** @test */
    function it_reverses_tag_list()
    {
        $mssql = app(MsSql::class);
        $dockerTags = M::mock(MicrosoftDockerTags::class, [app(Client::class), $mssql])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $dockerTags->shouldReceive('getTagsResponse')->andReturn(new MicrosoftDockerTagsFakestream('abc'));

        $this->assertEquals('2024-GA-ubuntu-18.04', $dockerTags->getLatestTag());
    }
}
