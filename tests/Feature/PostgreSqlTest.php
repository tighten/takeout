<?php

namespace Tests\Feature;

use App\Services\PostgreSql;
use Tests\TestCase;

class PostgreSqlTest extends TestCase
{
    /**
     * @test
     *
     * @testWith ["17.6", "/var/lib/postgresql/data"]
     *           ["16-3.5-alpine", "/var/lib/postgresql/data"]
     *           ["latest-pg16", "/var/lib/postgresql/data"]
     *           ["18.1-alpine", "/var/lib/postgresql"]
     *           ["pg18-trixie", "/var/lib/postgresql"]
     *           ["0.8.6-pg18-trixie", "/var/lib/postgresql"]
     *           ["19beta1-3.6-alpine", "/var/lib/postgresql"]
     *           ["alpine", "/var/lib/postgresql"]
     */
    public function it_mounts_the_volume_where_the_image_keeps_its_data($tag, $path)
    {
        $this->assertEquals($path, app(PostgreSql::class)->dataPath($tag));
    }
}
