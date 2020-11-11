![Takeout - Docker-based dependency management for macOS](takeout-banner.png?version=1)

# Takeout

[![Run tests](https://github.com/tighten/takeout/workflows/Run%20tests/badge.svg?branch=main)](https://github.com/tighten/takeout/actions?query=workflow%3A%22Run+tests%22)
[![Lint](https://github.com/tighten/takeout/workflows/Lint/badge.svg?branch=main)](https://github.com/tighten/takeout/actions?query=workflow%3ALint)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/tightenco/takeout.svg?style=flat)](https://packagist.org/packages/tightenco/takeout)
[![Downloads on Packagist](https://img.shields.io/packagist/dt/tightenco/takeout.svg?style=flat)](https://packagist.org/packages/tightenco/takeout)

Takeout is a CLI tool for spinning up tiny Docker containers, one for each of your development environment dependencies.

It's meant to be paired with a tool like [Laravel Valet](https://laravel.com/docs/valet). It's currently compatible with macOS, Linux, and WSL2.

With `takeout enable mysql` you're running MySQL, and never have to worry about managing or fixing Homebrew MySQL again.

But you can also easily enable ElasticSearch, PostgreSQL, MSSQL, Mongo, Redis, and more, with a simple command.

**Current list of services:**
- MySQL
- PostgreSQL
- MSSQL
- ElasticSearch
- MeiliSearch
- Redis
- Memcached
- MailHog
- MariaDB
- Minio
- Mongo
- InfluxDB
- DynamoDB
- Beanstalkd
- Sqs
- Neo4j
- Sftp

## Requirements

- macOS, Linux, or WSL2
- [Composer](https://getcomposer.org/) installed
- Docker installed (macOS: [Docker for Mac](https://docs.docker.com/docker-for-mac/))

## Installation

Install Takeout with Composer by running:

```bash
composer global require tightenco/takeout
```

Make sure the `~/.composer/vendor/bin` directory is in your system's "PATH".

## Usage

Run `takeout` and then a command name from anywhere in your terminal.

One of Takeout's primary benefits is that it boots ("enables") or deletes ("disables") Docker containers for your various dependencies quickly and easily.

Because Docker offers persistent volume storage, deleting a container (which we call "disabling" it) doesn't actually delete its data. That means you can enable and disable services with reckless abandon.

### Enable a service

Show a list of all services you can enable.

```bash
takeout enable
```

### Enable specific services

Passed the short name of one or more services, enable them.

```bash
takeout enable mysql

takeout enable redis meilisearch
```

### Enable services with default parameters

If you want to skip over being asked for each parameter and just accept the defaults. This also works with multiple services in one command.

```bash
takeout enable mysql --default

takeout enable redis meilisearch --default
```

### Disable a service

Show a list of all enabled services you can disable.

```bash
takeout disable
```

### Disable specific services

Passed the short name of one or more services, disable the enabled services that match them most closely.

```bash
takeout disable mysql

takeout disable redis meilisearch
```


### Disable all services

```bash
takeout disable --all
```

### Start a stopped container

Show a list of all stopped containers you can start.

```bash
takeout start
```

### Start a specific stopped container

Passed the container ID of stopped container, start the stopped container which matches it.

```bash
takeout start {container_id}
```

### Stop a running container

Show a list of all running containers you can stop.

```bash
takeout stop
```

### Stop a specific running container

Passed the container ID of running container, stop the running container which matches it.

```bash
takeout stop {container_id}
```

## Running multiple versions of a dependency

Another of Takeout's benefits is that it allows you to have multiple versions of a dependency installed and running at the same time. That means, for example, that you can run both MySQL 5.7 and 8.0 at the same time, on different ports.

Run `takeout enable mysql` twice; the first time, you'll want to choose the default port (`3306`) and the first version (`5.7`), and the second time, you'll want to choose a second port (`3307`), the second version (`8.0`) and a different volume name (so that they don't share the same `mysql_data`).

Now, if you run `takeout list`, you'll see both services running at the same time.

```bash
+--------------+----------------+---------------+-----------------------------------+
| CONTAINER ID | NAMES          | STATUS        | PORTS                             |
+--------------+----------------+---------------+-----------------------------------+
| 4bf3379ab2f5 | TO--mysql--5.7 | Up 2 seconds  | 33060/tcp, 0.0.0.0:3306->3306/tcp |
| 983acf46ceef | TO--mysql--8.0 | Up 35 seconds | 33060/tcp, 0.0.0.0:3307->3306/tcp |
+--------------+----------------+---------------+-----------------------------------+
```

## FAQs

<details>
<summary><strong>Will this enable the PHP drivers for me via PECL?</strong></summary>

Sadly, no.
</details>
<details>
<summary><strong>If I disable a service but Takeout still shows the port as taken, how do I proceed?</strong></summary>

First, run `lsof -i :3306` (where 3306 is the port that's unavailable.)

If you see output like this:

    com.docke   936 mattstauffer   52u  IPv6 0xc0d6f0b06d5c4efb      0t0  TCP localhost:mysql->localhost:62919 (FIN_WAIT_2)
    TablePlus 96155 mattstauffer   16u  IPv4 0xc0d6f0b0b6dccf6b      0t0  TCP localhost:62919->localhost:mysql (CLOSE_WAIT)

The solution is to just close your database GUI, and then it should be released.
</details>
<details>
<summary><strong>Why would you use this instead of `docker-compose`?</strong></summary>

Using `docker-compose` sets up your dependencies on a project-by-project basis, which is a perfectly fine way to do things. If it makes more sense to you to have a single copy of each of your dependencies for your entire global environment, Takeout makes more sense.
</details>
<details>
<summary><strong>Will disabling a service permanently delete my databases?</strong></summary>

Nope! Your data will stick around! By default almost all of our services use a "volume" to attach your data to for exactly this reason.

So, when you disable the MySQL service, for example, that volume--with all your data in it--will just sit there quietly. And when you re-enable, as long as you attach it to the same volume, all your data will still be there.
</details>

## Future plans

The best way to see our future plans is to check out the [Projects Board](https://github.com/tighten/takeout/projects/1), but here are a few plans for the future:

- Electron-based GUI
- `self-remove` command: Deletes all enabled services and then maybe self-uninstalls?
- `upgrade`: destroys the old container, brings up a new one with a newly-specified tag (prompt user for it, default `latest`) and keeps all other parameters (e.g. port, volume) exactly the same as the old one
- `pt/passthrough`: proxy commands through to docker (`./takeout pt mysql stop`)
- Deliver package in a way that's friendly to non-PHP developers (Homebrew? NPM?)
- Allow other people to extend Takeout by adding their own plugins (thanks to @angrybrad for the idea!)

## Process for release

If you're working with us and are assigned to push a release, here's the easiest process:


1. Visit the [Takeout Releases page](https://github.com/tighten/takeout/releases); figure out what your next tag will be (increase the third number if it's a patch or fix; increase the second number if it's adding features)
2. On your local machine, pull down the latest version of `main` (`git checkout main && git pull`)
3. Build for the version you're targeting (`php ./takeout app:build`)
4. Run the build once to make sure it works (`php ./builds/takeout list`)
5. Commit your build and push it up
6. [Draft a new release](https://github.com/tighten/takeout/releases/new) with both the tag version and release title of your tag (e.g. `v1.5.1`)
7. Set the body to be a bullet-point list with simple descriptions for each of the PRs merged, as well as the PR link in parentheses at the end. For example:

    `- Fix internal Memcached port (#92)`
8. Hit `Publish release`
9. Profit
