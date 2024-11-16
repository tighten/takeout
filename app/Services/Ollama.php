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
            'prompt' => 'Volume name (Ollama will store the models locally)',
            'default' => 'ollama_data',
        ],
        [
            'shortname' => 'cpuOnly',
            'prompt' => 'CPU only? Leave blank for yes. (Docker does not support GPUs on Mac)',
            'default' => '',
        ],
    ];

    protected $dockerRunTemplate = '-d --add-host=host.docker.internal:host-gateway --network-alias "takeout-ollama"  -v "${:volume}":/root/.ollama -p "${:port}":11434 "${:organization}"/"${:image_name}":"${:tag}"';

    protected function prompts(): void
    {
        parent::prompts();

        if (trim($this->promptResponses['cpuOnly']) !== '') {
            $this->dockerRunTemplate = '--gpus=all '.$this->dockerRunTemplate;
        }
    }
}
