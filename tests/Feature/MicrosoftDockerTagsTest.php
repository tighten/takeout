<?php

namespace Tests\Feature;

use App\Services\MsSql;
use App\Shell\MicrosoftDockerTags;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Stream as Psr7Stream;
use Mockery as M;
use Tests\TestCase;

class MicrosoftDockerTagsTest extends TestCase
{
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

class MicrosoftDockerTagsFakestream extends Psr7Stream
{
    public function __construct($stream, $options = [])
    {
        // Do nothing
    }

    public function __toString()
    {
        return json_encode([
            'name' => 'mssql/server',
            'tags' => [
                '2017-CU1-ubuntu',
                '2017-GDR3',
                '2019-RC1',
                '2019-GA-ubuntu-16.04',
                '2024-GA-ubuntu-18.04',
                'latest',
            ],
        ]);
    }
}
