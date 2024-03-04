# Event Snapshot Store

**⚠ Do not use it in production!⚠ This is still in development!**

This is a snapshot store for the [Phauthentic event sourcing library](https://github.com/phauthentic/event-sourcing).

## Installation

```sh
composer require phauthentic/snapshot-store
```

Running tests:

```sh
docker compose up
docker exec -it phpunit-container bin/phpunit
```

## Documentation

Please start by reading [docs/index.md](/docs/index.md) in this repository.

## License

Copyright Florian Krämer

Licensed under the [MIT license](license.txt).
