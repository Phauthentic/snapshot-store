# Event Sourcing Snapshot Store

This is a snapshot store for the [Phauthentic event sourcing library](https://github.com/phauthentic/event-sourcing).

Snapshotting is a technique used to reduce the number of events that need to be replayed to reconstitute an aggregate. Snapshots are taken periodically and stored in a snapshot store. When an aggregate is loaded, the snapshot is loaded first and then the events are replayed on top of the snapshot.


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
