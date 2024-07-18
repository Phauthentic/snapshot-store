<?php

declare(strict_types=1);

namespace Phauthentic\SnapshotStore\Store;

use Phauthentic\SnapshotStore\SnapshotInterface;

/**
 * Use this if you don't want to store snapshots.
 *
 * The reason this exists is that this way null-checks are not needed in repositories and that it becomes a
 * conscious decision to not store snapshots.
 */
class NullSnapshotStore implements SnapshotStoreInterface
{
    public function store(SnapshotInterface $snapshot): void
    {
    }

    public function get(string $aggregateId): ?SnapshotInterface
    {
        return null;
    }

    public function delete(string $aggregateId): void
    {
    }
}
