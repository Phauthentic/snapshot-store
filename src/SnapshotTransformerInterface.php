<?php

declare(strict_types=1);

namespace Phauthentic\SnapshotStore;

/**
 * SnapshotTransformerInterface
 */
interface SnapshotTransformerInterface
{
    /**
     * @param array<string, mixed> $data
     * @return SnapshotInterface
     */
    public function arrayToSnapshot(array $data): SnapshotInterface;

    /**
     * @param SnapshotInterface $snapshot
     * @return array<string, mixed>
     */
    public function snapshotToArray(SnapshotInterface $snapshot): array;
}
