<?php

namespace Tests\Support;

use GuzzleHttp\Psr7\Stream as Psr7Stream;

class MicrosoftDockerTagsFakestream extends Psr7Stream
{
    public function __construct($stream, $options = [])
    {
        // Do nothing
    }

    public function __toString()
    {
        try {
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
        catch (\Exception $e) {
            return '';
        }
    }
}
