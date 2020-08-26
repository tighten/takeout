![Takeout - Docker-based dependency management for macOS](takeout-banner.png?version=1)

# Takeout

Takeout is a Mac-based CLI tool for spinning up tiny Docker containers, one for each of your development environment dependencies.

It's meant to be paired with a tool like [Laravel Valet](https://laravel.com/docs/valet).

With `takeout enable mysql` you're running MySQL, and never have to worry about managing or fixing Homebrew MySQL again.

But you can also easily enable ElasticSearch, Postgres, MSSQL, Redis, and more, with a simple command.

**Current list of services:**
- MySQL
- Postgres
- MSSQL
- ElasticSearch
- MeiliSearch
- Redis
- Memcached

## Requirements

- macOS
- [Composer](https://getcomposer.org/) installed
- [Docker for Mac](https://docs.docker.com/docker-for-mac/) installed

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

### Enable a specific service

Passed the short name of a service, enable the given service.

```bash
takeout enable mysql
```

### Disable a service

Show a list of all enabled services you can disable.

```bash
takeout disable
```

### Disable a specific service

Passed the short name of a service, disable the enabled service which matches it most closely.
 
```bash
takeout disable mysql
```

## Running multiple versions of a dependency

Another of Takeout's benefits is that it allows you to have multiple versions of a dependency installed and running at the same time. That means, for example, that you can run MySQL 5.7 and 8.0 running at the same time, on different ports.

Run `takeout enable mysql` twice; the first time, you'll want to choose the default port (`3306`) and the first version (`5.7`), and the second time, you'll want to choose a second port (`3306`) and the second version (`8.0`).

Now, if you run `takeout list`, you'll see both services running at the same time.  

```bash
+--------------+--------------------------+-------------------+------------------------+-------------------------+
| CONTAINER ID | NAMES                    | STATUS            | PORTS                  |                         |
+--------------+--------------------------+-------------------+------------------------+-------------------------+
| eb5ab1fa055c | TO--mysql--8.0           | Up 53 seconds     | 33060/tcp              |  0.0.0.0:3307->3306/tcp |
| d02fe70db67f | TO--mysql--5.7           | Up About a minute | 0.0.0.0:3306->3306/tcp |  33060/tcp              |
+--------------+--------------------------+-------------------+------------------------+-------------------------+
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

## Future plans

The best way to see our future plans is to check out the [Projects Board](https://github.com/tightenco/takeout/projects/1), but here are a few plans for the future:

- Electron-based GUI
- `self-remove` command: Deletes all enabled services and then maybe self-uninstalls?
- `upgrade`: destroys the old container, brings up a new one with a newly-specified tag (prompt user for it, default `latest`) and keeps all other parameters (e.g. port, volume) exactly the same as the old one
- `pt/passthrough`: proxy commands through to docker (`./takeout pt mysql stop`)
- Deliver package in a way that's friendly to non-PHP developers (Homebrew? NPM?)
- Allow other people to extend Takeout by adding their own plugins (thanks to @angrybrad for the idea!)
