<?php

declare(strict_types=1);

namespace Phauthentic\SnapshotStore;

/**
 * Snapshot Factory
 */
interface SnapshotFactoryInterface
{
    /**
     * Creates a snapshot instance from array data.
     *
     * It's up to the implementation how this is validated.
     *
     * @param array<string, mixed> $array
     * @return SnapshotInterface
     */
    public function fromArray(array $array): SnapshotInterface;
}
