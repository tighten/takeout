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
        $services = new Services;
        $all = $services->all();

        $this->assertIsArray($all);
        $this->assertNotEmpty($all);
    }

    /** @test */
    function all_array_is_keyed_by_shortnames_and_values_are_fqcns()
    {
        $services = new Services;
        $all = collect($services->all());

        $filtered = collect($services->all())->filter(function ($fqcn, $shortname) {
            return Str::contains($fqcn, 'App\Services');
        })->filter(function ($fqcn, $shortname) {
            return !! preg_match('/^[a-z]+$/', $shortname);
        });

        $this->assertEquals($all->count(), $filtered->count());
    }

    /** @test */
    function it_excludes_base_service()
    {
        $services = new Services;
        $all = collect($services->all())->filter(function ($fqcn, $shortname) {
            return Str::contains($fqcn, 'BaseService');
        })->toArray();

        $this->assertEmpty($all);
    }

    /** @test */
    function it_tests_whether_services_exist_by_shortname()
    {
        $services = new Services;
        $this->assertFalse($services->exists('asdfasdfasdfsdaf'));
        $this->assertTrue($services->exists('meilisearch'));
    }

    /** @test */
    function it_gets_fqcn_by_shortname()
    {
        $services = new Services;
        $this->assertEquals('\App\Services\MeiliSearch', $services->get('meilisearch'));
    }

    /** @test */
    function it_throws_exceptions_if_asked_to_get_nonexistent_services()
    {
        $this->expectException(Exception::class);

        $services = new Services;
        $services->get('asdfasdfsdf');
    }
}
