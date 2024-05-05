<?php

namespace App\Shell;

use Composer\Semver\Comparator;

class VersionComparator
{
    public function __invoke(string $a, string $b): int
    {
        // Bump "a" if it is "latest"...
        if ($a === 'latest') {
            return -1;
        }

        // Bump "b" if it is "latest"...
        if ($b === 'latest') {
            return 1;
        }

        if ($this->startsAsSemver($a) && ! $this->startsAsSemver($b)) {
            return -1;
        }

        if ($this->startsAsSemver($b) && ! $this->startsAsSemver($a)) {
            return 1;
        }

        if ($this->stableSemver($a) && ! $this->stableSemver($b)) {
            return -1;
        }

        if ($this->stableSemver($b) && ! $this->stableSemver($a)) {
            return 1;
        }

        return Comparator::greaterThan(preg_replace('/^v/', '', $a), preg_replace('/^v/', '', $b)) ? -1 : 1;
    }

    private function stableSemver(string $version): bool
    {
        return preg_match('/^v?[\d.]+$/', $version);
    }

    private function startsAsSemver(string $version): bool
    {
        return preg_match('/^v?[\d.]+/', $version);
    }
}
