<?php

namespace App\Services;

use App\Shell\GitHubDockerTags;

class OpenWebui extends BaseService
{
    protected static $category = Category::TOOLS;

    protected $dockerTagsClass = GitHubDockerTags::class;

    protected $organization = 'ghcr.io';

    protected $imageName = 'open-webui/open-webui';

    protected $tag = 'main';

    protected $defaultPort = 3000;

    protected $prompts = [
        [
            'shortname' => 'volume',
            'prompt' => 'Volume Name (to store your app data, like chats, customizations, etc.)',
            'default' => 'webui_data',
        ],
        [
            'shortname' => 'cpu_only',
            'prompt' => 'CPU only? Leave blank for yes. (Docker does not support GPUs on Mac)',
            'default' => '',
        ],
        [
            'shortname' => 'ollama_server',
            'prompt' => 'Where is the Ollama server running?',
            'default' => 'http://takeout-ollama:11434',
        ],
    ];

    protected $dockerRunTemplate = '-d --add-host=host.docker.internal:host-gateway -e OLLAMA_BASE_URL="${:ollama_server}" -e WEBUI_AUTH=False -v "${:volume}":/app/backend/data -p "${:port}":8080 "${:organization}"/"${:image_name}":"${:tag}"';

    protected function prompts(): void
    {
        parent::prompts();

        if (trim($this->promptResponses['cpu_only']) !== '') {
            $this->dockerRunTemplate = '--gpus=all '.$this->dockerRunTemplate;
        }
    }
}
