<?php

namespace App\Services;

use App\Shell\MicrosoftDockerTags;
use App\Shell\Platform;

class MsSql extends BaseService
{
    protected static $category = Category::DATABASE;

    protected static $displayName = 'MS SQL Server';

    protected $organization = 'mcr.microsoft.com';
    protected $imageName = 'mssql/server';
    protected $dockerTagsClass = MicrosoftDockerTags::class;
    protected $defaultPort = 1433;
    protected $prompts = [
        [
            'shortname' => 'volume',
            'prompt' => 'What is the Docker volume name?',
            'default' => 'mssql_data',
        ],
        [
            'shortname' => 'sa_password',
            'prompt' => 'What will the password for the `sa` user be?',
            'default' => 'useA$strongPas1337',
        ],
    ];

    protected $dockerRunTemplate = '-p "${:port}":1433 \
        -e ACCEPT_EULA=Y \
        -e MSSQL_PID=Express \
        -e SA_PASSWORD="${:sa_password}" \
        -v "${:volume}":/var/opt/mssql \
        "${:organization}"/"${:image_name}":"${:tag}"';

    public function dockerRunTemplate(): string
    {
        // The Microsoft image doesn't provide a proper ARM64 build,
        // so we need to rely on Rosetta for Mac users. We can do
        // that by specifying a platform such as `linux/amd64`.

        return match (Platform::isArm()) {
            true => '--platform linux/amd64 \\' . PHP_EOL . $this->dockerRunTemplate,
            default => $this->dockerRunTemplate,
        };
    }
}
