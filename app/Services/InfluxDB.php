<?php

namespace App\Services;

class InfluxDB extends BaseService
{
    protected static $category = Category::DATABASE;

    protected $imageName = 'influxdb';
    protected $defaultPort = 8086;
    protected $prompts = [
        [
            'shortname' => 'volume',
            'prompt' => 'What is the Docker volume name?',
            'default' => 'influxdb_data',
        ],
        [
            'shortname' => 'admin_user',
            'prompt' => 'What will the admin user be?',
            'default' => 'admin',
        ],
        [
            'shortname' => 'admin_password',
            'prompt' => 'What will the admin password be?',
            'default' => 'password',
        ],
    ];

    protected $dockerRunTemplate = '-p "${:port}":8086 \
        -e INFLUXDB_ADMIN_USER="${:admin_user}" -e INFLUXDB_ADMIN_PASSWORD="${:admin_password}" \
        -v "${:volume}":/var/lib/influxdb \
        "${:organization}"/"${:image_name}":"${:tag}"';
}
