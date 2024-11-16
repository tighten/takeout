<?php

namespace App\Services;

class Ollama extends BaseService
{
    protected static $category = Category::TOOLS;

    protected $organization = 'ollama';

    protected $imageName = 'ollama';

    protected $defaultPort = 11434;

    protected $prompts = [
        [
            'shortname' => 'volume',
            'prompt' => 'Volume Name (Ollama will store the models locally)',
            'default' => 'ollama_data',
        ],
        [
            'shortname' => 'use_gpu',
            'prompt' => 'Do want to GPUs? Docker on Mac doesn\'t support GPUs. Default is "no", meaning CPU-only. To use GPUs, answer "yes".',
            'default' => 'no',
        ],
    ];

    protected $dockerRunTemplate = '-d --add-host=host.docker.internal:host-gateway \
        --network-alias "takeout-ollama" \
        -v "${:volume}":/root/.ollama \
        -p "${:port}":11434 \
        "${:organization}"/"${:image_name}":"${:tag}"';

    protected function prompts(): void
    {
        parent::prompts();

        if (! in_array(strtolower(trim($this->promptResponses['use_gpu'])), ['no', 'n', '0', 'false', ''])) {
            $this->dockerRunTemplate = '--gpus=all '.$this->dockerRunTemplate;
        }
    }
}
