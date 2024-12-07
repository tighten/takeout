<?php

namespace Tests\Support;

use App\Shell\DockerTags;

class FakePlatformDockerTags extends DockerTags
{
    const M1_ARM_PLATFORM = 'arm64';
    const LINUX_ARM_PLATFORM = 'aarch64';
    const INTEL_ARM_PLATFORM = 'x86_64';

    protected string $fakePlatform;

    public function withFakePlatform(string $platform): self
    {
        $this->fakePlatform = $platform;

        return $this;
    }

    protected function platform(): string
    {
        return $this->fakePlatform;
    }
}
