FROM composer:latest AS composer

FROM php:8.0-cli

COPY --from=composer /usr/bin/composer /usr/bin/composer

RUN apt-get update && apt-get install -y git
