# Takeout

The goal:

Inspired and co-written by Jose Soto (@josecanhelp),
for someone using a tool like Laravel Valet, which handles serving Laravel sites using PHP & Nginx,
make managing the installation and upgrading and deletion (and potentially running of multiple instances of the same service with different versions, just on different ports) of supporting services that would traditionally be, painfully, managed by Homebrew.

For example:

- MySQL
- Postgres
- MSSQL
- ElasticSearch
- MeiliSearch
- Redis
- Memcached

## In order for this app to actually work on your machine

- Docker for Mac installed

## Existential question:

- Does this app manage your PHP PECL stuff? No.

Matt idea:

two other apps

- PECL manager
- Wrapper around Valet, Lambo, Laravel Installer, PECL manager, and Takeout
    + Basically the entire local dev stack

## Brainstorming

How to get all running containers' names (so we can grep and filter out those not starting with our prefix):

```
docker ps --format "{{.Names}}" | grep 'to-*'
```

### Potential commands:

- install: pass service name or pick from menu
- list / installed: show all installed-by-takeout services, for each also show status (running/not running)
- uninstall: pass service name or pick from menu

- upgrade: v2: destroys old container, brings up a new one with a newly-specified tag (prompt user for it) and keeps all other parameters (e.g. port, volume) exactly the same as the old one
- pt/passthrough: proxy commands through to docker (`./takeout pt mysql stop`)

## Future plans

If this takes off, re-write it in non-PHP so non-PHP people can use it more easily.
