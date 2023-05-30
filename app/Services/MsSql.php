<?php

namespace App\Services;

use App\Shell\MicrosoftDockerTags;

class MsSql extends BaseService
{
    protected static $category = Category::DATABASE;

    protected $organization = 'mcr.microsoft.com';
    protected $imageName = 'azure-sql-edge';
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
        -e SA_PASSWORD="${:sa_password}" \
        -v "${:volume}":/var/opt/mssql \
        "${:organization}"/"${:image_name}":"${:tag}"';

    protected static $displayName = 'MS SQL Server';
}
