<?php

namespace Tests\Feature;

use App\Services\MsSql;
use App\Shell\MicrosoftDockerTags;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use Mockery as M;
use Tests\Testcase;

class MicrosoftDockerTagsTest extends TestCase
{
    /** @test */
    function it_gets_a_general_availability_release()
    {
        $mssql = app(MsSql::class);
        $dockerTags = app(MicrosoftDockerTags::class, ['service' => $mssql]);
        $output = $dockerTags->getLatestTag();

        $this->assertTrue(Str::contains($output, '-GA-'));
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

        $tag = $dockerTags->getLatestTag();

        $this->assertEquals('2024-GA-ubuntu-18.04', $tag);
    }
}

class MicrosoftDockerTagsFakestream extends \GuzzleHttp\Psr7\Stream
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
