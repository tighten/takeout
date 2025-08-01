<?php

namespace App\Services;

use App\Shell\Docker;
use App\Shell\DockerTags;
use App\Shell\Environment;
use App\Shell\Shell;
use App\WritesToConsole;
use Exception;
use Illuminate\Support\Str;
use Throwable;

abstract class BaseService
{
    use WritesToConsole;

    protected static $category;
    protected static $displayName;

    protected $organization = 'library'; // Official repositories use `library` as the organization name.
    protected $imageName;
    protected $dockerTagsClass = DockerTags::class;
    protected $tag;
    protected $dockerRunTemplate;
    protected $defaultPort;
    protected $defaultPrompts = [
        [
            'shortname' => 'port',
            'prompt' => 'Which host port would you like %s to use?',
            // Default is set in the constructor
        ],
        [
            'shortname' => 'tag',
            'prompt' => 'Which tag (version) of %s would you like to use?',
            'default' => 'latest',
        ],
    ];
    protected $prompts = [];
    protected $promptResponses = [];
    protected $shell;
    protected $environment;
    protected $docker;
    protected $useDefaults = false;

    public function __construct(Shell $shell, Environment $environment, Docker $docker)
    {
        $this->shell = $shell;
        $this->environment = $environment;
        $this->docker = $docker;

        $this->defaultPrompts = array_map(function ($prompt) {
            if ($prompt['shortname'] === 'port') {
                $prompt['default'] = $this->defaultPort;
            }

            return $prompt;
        }, $this->defaultPrompts);

        $this->promptResponses = [
            'organization' => $this->organization,
            'image_name' => $this->imageName,
        ];
    }

    public static function name(): string
    {
        return static::$displayName ?? Str::afterLast(static::class, '\\');
    }

    public static function category(): string
    {
        return static::$category ?? 'Other';
    }

    public function enable(bool $useDefaults = false, array $passthroughOptions = [], ?string $runOptions = null): void
    {
        $this->useDefaults = $useDefaults;

        $this->prompts();

        $this->ensureImageIsDownloaded();

        $this->info("Enabling {$this->shortName()}...\n");

        try {
            $this->docker->bootContainer(
                implode(' ', array_filter([
                    $runOptions,
                    $this->sanitizeDockerRunTemplate($this->dockerRunTemplate()),
                    $this->buildPassthroughOptionsString($passthroughOptions),
                ])),
                $this->buildParameters(),
            );

            $this->info("\nService enabled!");
        } catch (Throwable $e) {
            $this->error("\n" . $e->getMessage());
        }
    }

    public function forwardShell(): void
    {
        if (! $this->docker->isDockerServiceRunning()) {
            throw new Exception('Docker is not running.');
        }

        $service = $this->docker->takeoutContainers()->first(function ($container) {
            return str_starts_with($container['names'], "TO--{$this->shortName()}--");
        });

        if (! $service) {
            throw new Exception(sprintf('Service %s is not enabled.', $this->shortName()));
        }

        $this->docker->forwardShell($service['container_id'], $this->shellCommand());
    }

    public function organization(): string
    {
        return $this->organization;
    }

    public function imageName(): string
    {
        return $this->imageName;
    }

    public function shortName(): string
    {
        return strtolower(class_basename(static::class));
    }

    public function dockerRunTemplate(): string
    {
        return $this->dockerRunTemplate;
    }

    public function defaultPort(): int
    {
        return $this->defaultPort;
    }

    public function sanitizeDockerRunTemplate($dockerRunTemplate): string
    {
        if ($this->environment->isWindowsOs()) {
            return stripslashes($dockerRunTemplate);
        }

        return $dockerRunTemplate;
    }

    public function buildPassthroughOptionsString(array $passthroughOptions): string
    {
        if (empty($passthroughOptions)) {
            return '';
        }

        return implode(' ', $passthroughOptions);
    }

    protected function shellCommand(): string
    {
        return 'bash';
    }

    protected function ensureImageIsDownloaded(): void
    {
        if ($this->docker->imageIsDownloaded($this->organization, $this->imageName, $this->tag)) {
            return;
        }

        $this->info("Downloading docker image...\n");
        $this->docker->downloadImage($this->organization, $this->imageName, $this->tag);
    }

    protected function prompts(): void
    {
        $items = [];

        $questions = array_merge($this->defaultPrompts, $this->prompts);

        foreach ($questions as $prompt) {
            $items[] = match (true) {
                Str::contains($prompt['shortname'], 'port') => $this->askQuestion($prompt, $this->useDefaults, validate: function (string $port) {
                    if (! is_numeric($port)) {
                        return 'Sorry, the port must be a valid number.';
                    }

                    if (! $this->environment->portIsAvailable($port)) {
                        return "Port {$port} is already in use. Please select a different port.";
                    }

                    return null;
                }),
                Str::contains($prompt['shortname'], 'volume') => $this->askQuestion($prompt, $this->useDefaults, validate: function (string $volume) {
                    if (! $this->docker->volumeIsAvailable($volume)) {
                        return "Volume {$volume} is already in use. Please select a different volume.";
                    }

                    return null;
                }),
                default => $this->askQuestion($prompt, $this->useDefaults),
            };
        }

        // Allow users to pass custom docker images (e.g. "postgis/postgis:latest") when we ask for the tag
        if (Str::is('*:*', $this->promptResponses['tag'])) {
            [$image, $this->promptResponses['tag']] = explode(':', $this->promptResponses['tag']);
            [$this->promptResponses['organization'], $this->promptResponses['image_name']] = Str::is('*/*', $image) ? explode('/', $image) : [$this->organization, $image];
        }

        $this->tag = $this->resolveTag($this->promptResponses['tag']);
    }

    protected function askQuestion(array $prompt, $useDefaults = false, $validate = null): void
    {
        $this->promptResponses[$prompt['shortname']] = $prompt['default'] ?? null;

        if (! $useDefaults) {
            $this->promptResponses[$prompt['shortname']] = $this->ask(sprintf($prompt['prompt'], $this->imageName), $prompt['default'] ?? null, $validate);
        }
    }

    protected function resolveTag($responseTag): string
    {
        return app()->make($this->dockerTagsClass, ['service' => $this])->resolveTag($responseTag);
    }

    protected function buildParameters(): array
    {
        $parameters = $this->promptResponses;
        $parameters['container_name'] = $this->containerName();
        $parameters['alias'] = $this->shortNameWithVersion();
        $parameters['tag'] = $this->tag; // Overwrite "latest" with actual latest tag

        return $parameters;
    }

    protected function shortNameWithVersion(): string
    {
        // Check if tag represents semantic version (v5.6.0, 5.7.4, or 8.0) and return major.minor
        // (eg mysql5.7) or return the actual tag prefixed by a dash (eg redis-buster)
        if (! preg_match('/v?(0|(?:[1-9]\d*))(?:\.(0|(?:[1-9]\d*))(?:\.(0|(?:[1-9]\d*)))?)/', $this->tag)) {
            return $this->shortName() . "-{$this->tag}";
        }

        $version = trim($this->tag, 'v');
        [$major, $minor] = explode('.', $version);

        return $this->shortName() . "{$major}.{$minor}";
    }

    protected function containerName(): string
    {
        $portTag = '';
        foreach ($this->promptResponses as $key => $value) {
            if (Str::contains($key, 'port')) {
                $portTag .= "--{$value}";
            }
        }

        return 'TO--' . $this->shortName() . '--' . $this->tag . $portTag;
    }
}
