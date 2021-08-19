<?php

namespace Tests\Support;

use GuzzleHttp\Psr7\Stream as Psr7Stream;

class MinioDockerTagsFakestream extends Psr7Stream
{
    public function __construct($stream, $options = [])
    {
        // Do nothing
    }

    public function getContents()
    {
        return json_encode([
            'results' => [
                ['name' => 'latest'],
                ['name' => 'edge-cicd'],
                ['name' => 'edge'],
                ['name' => 'RELEASE.2021-08-05T22-01-19Z.fips'],
                ['name' => 'RELEASE.2021-08-05T22-01-19Z'],
                ['name' => 'RELEASE.2021-07-30T00-02-00Z.fips'],
                ['name' => 'RELEASE.2021-07-30T00-02-00Z'],
                ['name' => 'RELEASE.2021-07-27T02-40-15Z.fips'],
                // Moved these two to the bottom to test our sorting
                ['name' => 'RELEASE.2021-08-17T20-53-08Z.fips'],
                ['name' => 'RELEASE.2021-08-17T20-53-08Z'],
                ['name' => '11563-d95ceb3'],
                ['name' => '11466-bcfe28a'],
            ],
        ]);
    }
}
