#!/usr/bin/env bash

set -ex

# Ensure the tighten/takeout-builder docker image is up-to-date with the Dockerfile...
docker buildx build . -t tighten/takeout-builder

# Create the composer cache volumes to speed things up a bit on subsequent builds...
docker volume create takeout-build-composer-local-cache
docker volume create takeout-build-composer-global-cache

# We need to ensure the vendor/ folder and the composer.lock are not
# present so we can get a clean dependencies install...
rm -rf vendor/ composer.lock

# Pull dependencies using the correct PHP version and build!
docker run --rm -it \
    -v $PWD:/app \
    -v takeout-build-composer-local-cache:/app/vendor \
    -v takeout-build-composer-global-cache:/composer \
    -w /app \
    -e COMPOSER_HOME=/composer \
    tighten/takeout-builder bash -c "composer install && php ./takeout app:build"
