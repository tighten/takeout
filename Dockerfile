FROM php:8.2-cli-alpine

ENV TAKEOUT_CONTAINER=1

COPY --from=docker/buildx-bin /buildx /usr/libexec/docker/cli-plugins/docker-buildx

# Install the PHP extensions & Docker
RUN apk add --no-cache --update docker openrc ncurses \
    && apk add --no-cache linux-headers \
    && docker-php-ext-install -j$(nproc) sockets \
    && rc-update add docker boot

WORKDIR /takeout

COPY builds/takeout /usr/local/bin/takeout

ENTRYPOINT ["takeout"]

