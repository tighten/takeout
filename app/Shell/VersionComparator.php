<?php

namespace App\Shell;

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

        // If both versions are regular semver versions (numbers and dots only), we'll compare them as semver...
        if ($this->isSemver($a) && $this->isSemver($b)) {
            return $this->compareSemver($a, $b);
        }

        // Bump "a" if it is semver and "b" is not...
        if ($this->isSemver($a)) {
            return -1;
        }

        // Bump "b" if it is semver and "a" is not...
        if ($this->isSemver($b)) {
            return 1;
        }

        // Otherwise, compare alphabetically...
        return strcmp($a, $b);
    }

    private function isSemver(string $version): bool
    {
        return preg_match('/^[\d.]+$/', $version);
    }

    private function compareSemver($a, $b)
    {
        // We're going to split up the versions in parts and we'll compare each
        // part individually. If any of the version doesn't contain the part,
        // we'll consider any missing part as higher then the specific one.

        $aParts = explode('.', $a);
        $bParts = explode('.', $b);

        $maxParts = max(count($aParts), count($bParts));

        foreach (range(0, $maxParts) as $depth) {
            $aValue = isset($aParts[$depth]) ? intval($aParts[$depth]) : PHP_INT_MAX;
            $bValue = isset($bParts[$depth]) ? intval($bParts[$depth]) : PHP_INT_MAX;

            if ($aValue === $bValue) {
                continue;
            }

            return $aValue > $bValue ? -1 : 1;
        }

        return 0;
    }
}
