FROM php:8.1-cli-alpine

COPY --from=docker/buildx-bin /buildx /usr/libexec/docker/cli-plugins/docker-buildx

# Install the PHP extensions & Docker
RUN apk add --no-cache --update docker openrc \
    && docker-php-ext-configure pcntl --enable-pcntl \
    && docker-php-ext-install -j$(nproc) pcntl \
    && rc-update add docker boot

WORKDIR /takeout

COPY builds/takeout /usr/local/bin/takeout

ENTRYPOINT ["takeout"]
