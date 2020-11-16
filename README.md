![Takeout - Docker-based dependency management](takeout-banner.png?version=1)

Docker-based development-only dependency management.

[![oclif](https://img.shields.io/badge/cli-oclif-brightgreen.svg)](https://oclif.io)
[![Version](https://img.shields.io/npm/v/takeout.svg)](https://npmjs.org/package/takeout)
[![Downloads/week](https://img.shields.io/npm/dw/takeout.svg)](https://npmjs.org/package/takeout)
[![License](https://img.shields.io/npm/l/takeout.svg)](https://github.com/tighten/takeout/blob/master/package.json)

> NOTE: This branch is for the Node port. We've never written Node CLI apps. It's gonna take a while.

Takeout is a CLI tool for spinning up tiny Docker containers, one for each of your development environment dependencies.

It's meant to be paired with a tool like [Laravel Valet](https://laravel.com/docs/valet). It's currently compatible with macOS, Linux, and WSL2.

With `takeout enable mysql` you're running MySQL, and never have to worry about managing or fixing Homebrew MySQL again.

But you can also easily enable ElasticSearch, PostgreSQL, MSSQL, Mongo, Redis, and more, with a simple command. See the full list here: TODO

<!-- toc -->
* [Requirements](#requirements)
* [Usage](#usage)
* [Commands](#commands)
<!-- tocstop -->

# Requirements

- macOS, Linux, or WSL2
- Node installed or whatever
- Docker installed (macOS: [Docker for Mac](https://docs.docker.com/docker-for-mac/))

# Usage
<!-- usage -->
```sh-session
$ npm install -g takeout
$ takeout COMMAND
running command...
$ takeout (-v|--version|version)
takeout/2.0.0-beta.0 darwin-x64 node-v11.6.0
$ takeout --help [COMMAND]
USAGE
  $ takeout COMMAND
...
```

Run `takeout` and then a command name from anywhere in your terminal.

One of Takeout's primary benefits is that it boots ("enables") or deletes ("disables") Docker containers for your various dependencies quickly and easily.

Because Docker offers persistent volume storage, deleting a container (which we call "disabling" it) doesn't actually delete its data. That means you can enable and disable services with reckless abandon.
<!-- usagestop -->

# Commands
<!-- commands -->
* [`takeout hello [FILE]`](#takeout-hello-file)
* [`takeout help [COMMAND]`](#takeout-help-command)

## `takeout hello [FILE]`

describe the command here

```
USAGE
  $ takeout hello [FILE]

OPTIONS
  -f, --force
  -h, --help       show CLI help
  -n, --name=name  name to print

EXAMPLE
  $ takeout hello
  hello world from ./src/hello.ts!
```

_See code: [src/commands/hello.ts](https://github.com/tighten/takeout/blob/v2.0.0alpha1/src/commands/hello.ts)_

## `takeout help [COMMAND]`

display help for takeout

```
USAGE
  $ takeout help [COMMAND]

ARGUMENTS
  COMMAND  command to show help for

OPTIONS
  --all  see all commands in CLI
```

_See code: [@oclif/plugin-help](https://github.com/oclif/plugin-help/blob/v3.2.0/src/commands/help.ts)_
<!-- commandsstop -->



# Goal docs (from original PHP) that don't work yet

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

