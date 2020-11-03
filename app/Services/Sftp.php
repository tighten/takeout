<?php

namespace App\Services;

use App\Services\BaseService;
use App\Services\Category;
use App\Shell\Docker;
use App\Shell\Environment;
use App\Shell\Shell;

class Sftp extends BaseService
{
    protected static $category = Category::STORAGE;

    protected $organization = 'atmoz';
    protected $imageName = 'sftp';
    protected $defaultPort = 22;
    protected $tag = 'alpine';
    protected $prompts = [
        [
            'shortname' => 'user_name',
            'prompt' => 'What will the default username be?',
            'default' => 'foo',
        ],
        [
            'shortname' => 'password',
            'prompt' => 'What will the default password be?',
            'default' => 'pass',
        ],
        [
            'shortname' => 'upload_directory',
            'prompt' => 'Where will files be uploaded?',
            'default' => 'upload',
        ],
        [
            'shortname' => 'mapped_directory',
            'prompt' => 'Which local directory should be mapped inside? (nothing if null)',
            'default' => '',
        ],
    ];

    protected $dockerRunTemplate = '-p "${:port}":22 \
        "${:organization}"/"${:image_name}":"${:tag}" \
        "${:user_config}"';

    protected static $displayName = 'SFTP';

    public function __construct(Shell $shell, Environment $environment, Docker $docker) {
        parent::__construct($shell, $environment, $docker);

        $this->defaultPrompts = array_map(function ($prompt) {
            if ($prompt['shortname'] === 'tag') {
                $prompt['default'] = $this->tag;
            }

            return $prompt;
        }, $this->defaultPrompts);
    }

    protected function prompts(): void
    {
        parent::prompts();

        if($this->promptResponses['mapped_directory'] !== "") {
            $this->dockerRunTemplate = '-v "${:local_mapping}" ' . $this->dockerRunTemplate;
        }
    }

    protected function buildParameters(): array
    {
        $parameters = parent::buildParameters();

        if($parameters['mapped_directory'] !== "") {
            $parameters['local_mapping'] = trim($parameters['mapped_directory'], ' ') . ':/home/' . $parameters['user_name'] . '/' . $parameters['upload_directory'];
            $parameters['user_config'] = $parameters['user_name'] . ':' . $parameters['password'] . ':1001';
        } else {
            $parameters['user_config'] = $parameters['user_name'] . ':' . $parameters['password'] . ':::' . $parameters["upload_directory"];
        }

        return $parameters;
    }
}
