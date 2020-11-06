<?php

namespace App\Shell;

use Illuminate\Support\Collection;

class DockerFormatter
{
    /**
     * Given the raw string of output from Docker, return a collection of
     * associative arrays, with the keys lowercased and slugged using underscores
     *
     * @param string $output Docker command output
     * @return Collection     Collection of associative arrays
     */
    // @todo test this
    public function rawTableOutputToCollection($output): Collection
    {
        $containers = collect(explode("\n", trim($output)))->map(function ($line) {
            return explode('|', $line);
        })->filter();

        $keys = array_map('App\underscore_slug', $containers->shift());

        if ($containers->isEmpty()) {
            return $containers;
        }

        return $containers->map(function ($container) use ($keys) {
            return array_combine($keys, $container);
        });
    }
}
