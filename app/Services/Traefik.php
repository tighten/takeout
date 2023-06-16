<?php

namespace App\Services;

use App\Shell\Docker;
use App\Shell\Environment;
use App\Shell\Shell;
use Exception;

class Traefik extends BaseService
{
    protected static $category = Category::TOOLS;

    protected $imageName = 'traefik';

    protected $defaultTag = 'v2.10';

    protected $defaultPort = 8080;

    protected $prompts = [
        [
            'shortname' => 'config_dir',
            'prompt' => 'What is the configuration directory?',
        ],
        [
            'shortname' => 'web_port',
            'prompt' => 'What is the web port?',
            'default' => '80',
        ],
        [
            'shortname' => 'websecure_port',
            'prompt' => 'What is the websecure port?',
            'default' => '443',
        ],
    ];

    protected $dockerRunTemplate = '-p "${:port}":8080 -p "${:web_port}":80 -p "${:websecure_port}":443 \
        -v "${:config_dir}":/etc/traefik \
        -v /var/run/docker.sock:/var/run/docker.sock \
        "${:organization}"/"${:image_name}":"${:tag}" \
        --api.insecure=true --providers.docker=true --entryPoints.web.address=:80 --entryPoints.websecure.address=:443 \
        --providers.file.directory=/etc/traefik/conf --providers.file.watch=true';

    public function __construct(Shell $shell, Environment $environment, Docker $docker)
    {
        parent::__construct($shell, $environment, $docker);

        $home = $this->environment->homeDirectory();

        $this->defaultPrompts = array_map(function ($prompt) {
            if ($prompt['shortname'] === 'tag') {
                $prompt['default'] = $this->defaultTag;
            }

            return $prompt;
        }, $this->defaultPrompts);

        $this->prompts = array_map(function ($prompt) use ($home) {
            if ($prompt['shortname'] === 'config_dir' && ! empty($home)) {
                $prompt['default'] = "{$home}/.config/traefik";
            }

            return $prompt;
        }, $this->prompts);
    }

    protected function prompts(): void
    {
        parent::prompts();

        if (empty($this->promptResponses['config_dir'])) {
            throw new Exception('You must specify a configuration directory.');
        }
    }
}
