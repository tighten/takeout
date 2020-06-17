![GitHub Tests Status](https://img.shields.io/github/workflow/status/tightenco/takeout/run-tests?label=Tests)
# <img src="takeout-container.png" alt="Takeout container" align="right"> Takeout

Takeout is a Mac-based CLI tool for spinning up tiny Docker containers, one for each of your development environment dependencies.

With `takeout install mysql` you're running MySQL, and never have to worry about Homebrew MySQL again.

But you can also easily install ElasticSearch, Postgres, MSSQL, Redis, and more, with a simple command.

## History

Tighten programmer [Jose Soto](https://twitter.com/josecanhelp) has long been advocating ([podcast](https://twentypercenttime.simplecast.com/episodes/jose-soto-docker-for-local-development), [Laracasts](https://laracasts.com/series/guest-spotlight/episodes/2)) the usage of simple, small Docker containers for local development dependencies. Instead of building your entire local dev stack using something like Vessel, you use your existing web server (likely Laravel Valet) but rely on Docker for managing your services like MySQL and Redis.

Tighten programmer [Matt Stauffer](https://twitter.com/stauffermatt) thought of the idea of packaging Jose's way of working with Docker up into a simple command-line tool, and Takeout was born.

**Example services:**

- MySQL
- Postgres
- MSSQL
- ElasticSearch
- MeiliSearch
- Redis
- Memcached

## Requirements

- Docker for Mac installed

## Installation

@todo

## Usage

### Install with no params

```bash
takeout install
```

Presents you with a menu of potential services to install.

### Install passing service name

```bash
takeout install mysql
```

Installs this service if possible

### Uninstall with no params

```bash
takeout uninstall
```

Presents you with a list of your installed services, and you can pick one to uninstall.

### Uninstall with params

```bash
takeout uninstall mysql
```

Uninstalls this service if possible.

## Brainstorming

How to get all running containers' names (so we can grep and filter out those not starting with our prefix):

```bash
docker ps --format "{{.Names}}" | grep 'TO-*'
```

### Potential commands:

- install: pass service name or pick from menu
- list: show all installed-by-takeout services, for each also show status (running/not running)
- uninstall: pass service name or pick from menu
- self-remove: Deletes all installed services and then maybe self-uninstalls?

## Future plans

- upgrade: v2: destroys old container, brings up a new one with a newly-specified tag (prompt user for it) and keeps all other parameters (e.g. port, volume) exactly the same as the old one
- pt/passthrough: proxy commands through to docker (`./takeout pt mysql stop`)
- Deliver package in a way that's friendly to non-PHP developers
- Add 'upgrade' command, which saves the config settings for the old one, brings up a new one with the same tag and parameters, and allows you to re-specify the version constraint
- Allow for more than one of each service (e.g. mysql 5.7, mysql 8, another mysql 8, etc.)

## FAQs

<details>
    <summary><strong>Will this install the PHP drivers for me via PECL?</strong></summary>

    Sadly, no.
</details>

## Todo

See our [Project Board](https://github.com/tightenco/takeout/projects/1) for tasks.
