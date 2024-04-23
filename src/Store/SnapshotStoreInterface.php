<?php

declare(strict_types=1);

namespace Phauthentic\SnapshotStore\Store;

use Phauthentic\SnapshotStore\SnapshotInterface;

/**
 * SnapshotStoreInterface
 */
interface SnapshotStoreInterface
{
    public const DATE_FORMAT = 'Y-m-d H:i:s';

    /**
     * Stores an aggregate snapshot
     *
     * @param SnapshotInterface $snapshot Snapshot
     * @return void
     */
    public function store(SnapshotInterface $snapshot);

    /**
     * Gets an aggregate snapshot if one exist
     *
     * @return null|SnapshotInterface
     */
    public function get(string $aggregateId): ?SnapshotInterface;

    /**
     * Removes an aggregate from the store
     *
     * @return void
     */
    public function delete(string $aggregateId): void;
}
