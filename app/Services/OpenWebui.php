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
            'default' => 'openwebui_data',
        ],
        [
            'shortname' => 'use_gpu',
            'prompt' => 'Do want to use your GPUs? Docker on Mac doesn\'t support GPUs. Default is "no", meaning CPU-only. To use GPUs, answer "yes".',
            'default' => 'no',
        ],
        [
            'shortname' => 'ollama_server',
            'prompt' => 'Where is the Ollama server running?',
            'default' => 'http://takeout-ollama:11434',
        ],
    ];

    protected $dockerRunTemplate = '-d --add-host=host.docker.internal:host-gateway \
        -e OLLAMA_BASE_URL="${:ollama_server}" \
        -e WEBUI_AUTH=False \
        -v "${:volume}":/app/backend/data \
        -p "${:port}":8080 \
        "${:organization}"/"${:image_name}":"${:tag}"';

    protected function prompts(): void
    {
        parent::prompts();

        if (! in_array(strtolower(trim($this->promptResponses['use_gpu'])), ['no', 'n', '0', 'false', ''])) {
            $this->dockerRunTemplate = '--gpus=all '.$this->dockerRunTemplate;
        }
    }
}
