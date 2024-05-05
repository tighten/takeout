<?php

namespace Tests\Feature;

use App\Shell\VersionComparator;
use Tests\TestCase;

class VersionComparatorTest extends TestCase
{
    /**
     * @test
     *
     * @testWith [["latest", "8.0"], ["latest", "8.0"]]
     *           [["8.0", "latest"], ["latest", "8.0"]]
     *           [["5.0", "8.0", "latest"], ["latest", "8.0", "5.0"]]
     *           [["8.0.43", "8.0", "latest"], ["latest", "8.0.43", "8.0"]]
     *           [["8.0.4-oraclelinux8", "8.0.4", "latest"], ["latest", "8.0.4", "8.0.4-oraclelinux8"]]
     *           [["8.0.4-oraclelinux8", "5.0.1", "8.0.4", "latest", "5.0.1-oraclelinux8"], ["latest", "8.0.4", "5.0.1", "8.0.4-oraclelinux8", "5.0.1-oraclelinux8"]]
     */
    public function compares($versions, $expectedOrder)
    {
        $this->assertEquals($expectedOrder, $this->sort($versions));
    }

    private function sort(array $versions): array
    {
        return collect($versions)
            ->sort(new VersionComparator)
            ->values()
            ->all();
    }
}
