<?php

namespace App\Services;

class PostgreSql extends BaseService
{
    protected static $category = Category::DATABASE;

    protected $imageName = 'postgres';
    protected $defaultPort = 5432;
    protected $prompts = [
        [
            'shortname' => 'volume',
            'prompt' => 'What is the Docker volume name?',
            'default' => 'postgres_data',
        ],
        [
            'shortname' => 'root_password',
            'prompt' => 'What will the password for the `postgres` user be?',
            'default' => 'password',
        ],
    ];

    protected $dockerRunTemplate = '-p "${:port}":5432 \
        -e POSTGRES_PASSWORD="${:root_password}" \
        -v "${:volume}":"${:data_path}" \
            "${:organization}"/"${:image_name}":"${:tag}"';

    protected static $displayName = 'PostgreSQL';

    protected function buildParameters(): array
    {
        $parameters = parent::buildParameters();

        $parameters['data_path'] = $this->dataPath($this->tag);

        return $parameters;
    }

    // Postgres 18 moved the data volume; mounting the old path leaves the named volume empty.
    public function dataPath(string $tag): string
    {
        // "pgNN" beats a leading 0.8.6-style version; non-numeric tags track the newest major.
        if (! preg_match('/^(?:.*?pg)?(\d+)/', $tag, $matches)) {
            return '/var/lib/postgresql';
        }

        return (int) $matches[1] >= 18 ? '/var/lib/postgresql' : '/var/lib/postgresql/data';
    }
}
