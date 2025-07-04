<?php

namespace App\Shell;

class Platform
{
    public static array $armArchitectures = ['arm64', 'aarch64'];

    public function __construct(
        private ?string $platform = null,
    ) {}

    public static function make(): static
    {
        return resolve(Platform::class);
    }

    public static function fake(string $platform): Platform
    {
        return app()->instance(Platform::class, new Platform($platform));
    }

    public static function isArm(): bool
    {
        return in_array(static::make()->platform(), static::$armArchitectures, true);
    }

    protected function platform(): string
    {
        return $this->platform ??= php_uname('m');
    }
}
