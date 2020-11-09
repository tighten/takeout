<?php

namespace Tests\Feature;

use App\Services;
use Exception;
use Illuminate\Support\Str;
use Tests\TestCase;

class ServicesTest extends TestCase
{
    /** @test */
    function it_returns_an_array_from_all()
    {
        $services = app(Services::class);
        $all = $services->all();

        $this->assertIsArray($all);
        $this->assertNotEmpty($all);
    }

    /** @test */
    function all_array_is_keyed_by_shortnames_and_values_are_fqcns()
    {
        $services = app(Services::class);
        $all = collect($services->all());

        $filtered = collect($services->all())->filter(function ($fqcn, $shortname) {
            return Str::contains($fqcn, 'App\Services');
        })->filter(function ($fqcn, $shortname) {
            return !! preg_match('/^[a-z0-9]+$/', $shortname);
        });

        $this->assertEquals($all->count(), $filtered->count());
    }

    /** @test */
    function it_excludes_base_service()
    {
        $services = app(Services::class);
        $all = collect($services->all())->filter(function ($fqcn, $shortname) {
            return Str::contains($fqcn, 'BaseService');
        })->toArray();

        $this->assertEmpty($all);
    }

    /** @test */
    function it_tests_whether_services_exist_by_shortname()
    {
        $services = app(Services::class);
        $this->assertFalse($services->exists('asdfasdfasdfsdaf'));
        $this->assertTrue($services->exists('meilisearch'));
    }

    /** @test */
    function it_gets_fqcn_by_shortname()
    {
        $services = app(Services::class);
        $this->assertEquals('App\Services\MeiliSearch', $services->get('meilisearch'));
    }

    /** @test */
    function it_throws_exceptions_if_asked_to_get_nonexistent_services()
    {
        $this->expectException(Exception::class);

        $services = app(Services::class);
        $services->get('asdfasdfsdf');
    }
}
