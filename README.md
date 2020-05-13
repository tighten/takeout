# Takeout

**The goal:**

Inspired and co-written by Jose Soto (@josecanhelp), for someone using a tool like Laravel Valet, which handles serving Laravel sites using PHP & Nginx, make managing the installation and upgrading and deletion (and potentially running of multiple instances of the same service with different versions, just on different ports) of supporting services that would traditionally be, painfully, managed by Homebrew.

**For example:**

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
- list / installed: show all installed-by-takeout services, for each also show status (running/not running)
- uninstall: pass service name or pick from menu
- ???: Deletes all installed services and then maybe self-uninstalls?

- upgrade: v2: destroys old container, brings up a new one with a newly-specified tag (prompt user for it) and keeps all other parameters (e.g. port, volume) exactly the same as the old one
- pt/passthrough: proxy commands through to docker (`./takeout pt mysql stop`)

## Future plans

- Deliver package in a way that's friendly to non-PHP developers
- Add 'upgrade' command, which saves the config settings for the old one, brings up a new one with the same tag and parameters, and allows you to re-specify the version constraint
- Allow for more than one of each service (e.g. mysql 5.7, mysql 8, another mysql 8, etc.)

## Existential question:

- Does this app manage your PHP PECL stuff? No.

Matt idea:

two other apps

- PECL manager
- Wrapper around Valet, Lambo, Laravel Installer, PECL manager, and Takeout
    + Basically the entire local dev stack
