<?php

namespace Tests\Feature;

use App\Services\MsSql;
use App\Shell\MicrosoftDockerTags;
use GuzzleHttp\Client;
use Mockery as M;
use Tests\Support\MicrosoftDockerTagsFakestream;
use Tests\TestCase;

class MicrosoftDockerTagsTest extends TestCase
{
    /** @test */
    function it_reverses_tag_list()
    {
        $mssql = app(MsSql::class);
        $dockerTags = M::mock(MicrosoftDockerTags::class, [app(Client::class), $mssql])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $dockerTags->shouldReceive('getTagsResponse')->andReturn(new MicrosoftDockerTagsFakestream('abc'));

        $this->assertEquals('latest', $dockerTags->getLatestTag());
    }
}
