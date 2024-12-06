<?php

namespace Tests\Feature;

use App\Services\Category;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    /**
     * @test
     *
     * @testWith ["mysql", "database"]
     *           ["ollama", "tools"]
     *           ["redis", "cache"]
     *           ["typesense", "search"]
     *           ["soketi", "sockets"]
     *           ["minio", "storage"]
     *           ["mailpit", "mail"]
     *           ["unknown", "other"]
     */
    public function resolves_category_from_service_name($serviceName, $category): void
    {
        $this->assertEquals($category, Category::fromServiceName($serviceName));
    }

    /**
     * @test
     *
     * @testWith ["TO--mysql--9.1.0--3306", "database"]
     *           ["TO--mailpit--v1.21.5--1025--8025", "mail"]
     *           ["TO--unknown--v1.20.0--8080", "other"]
     */
    public function resolves_category_from_container_names($containerName, $category): void
    {
        $this->assertEquals($category, Category::fromContainerName($containerName));
    }
}
