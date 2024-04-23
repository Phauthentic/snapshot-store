<?php

declare(strict_types=1);

namespace Phauthentic\SnapshotStore;

use DateTimeImmutable;
use Phauthentic\SnapshotStore\Exception\AssertionException;

/**
 * Snapshot Factory
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class SnapshotFactory implements SnapshotFactoryInterface
{
    /**
     * @param array<string, mixed> $data
     * @return void
     * @throws AssertionException
     */
    protected function assertArrayKeys(array $data): void
    {
        $keys = [
            SnapshotInterface::AGGREGATE_TYPE,
            SnapshotInterface::AGGREGATE_ID,
            SnapshotInterface::AGGREGATE_ROOT,
            SnapshotInterface::AGGREGATE_VERSION,
            SnapshotInterface::AGGREGATE_CREATED_AT,
        ];

        foreach ($keys as $key) {
            if (!isset($data[$key])) {
                throw AssertionException::missingArrayKey($key);
            }
        }
    }

    /**
     * @param array<string, mixed> $data
     * @throws \Phauthentic\SnapshotStore\Exception\SnapshotStoreException
     */
    public function fromArray(array $data): SnapshotInterface
    {
        $this->assertArrayKeys($data);

        return new Snapshot(
            aggregateType: $data[SnapshotInterface::AGGREGATE_TYPE],
            aggregateId: $data[SnapshotInterface::AGGREGATE_ID],
            aggregateRoot: $data[SnapshotInterface::AGGREGATE_ROOT],
            lastVersion: $data[SnapshotInterface::AGGREGATE_VERSION],
            createdAt: is_string($data[SnapshotInterface::AGGREGATE_CREATED_AT]) ?
                new DateTimeImmutable($data[SnapshotInterface::AGGREGATE_CREATED_AT]) :
                $data[SnapshotInterface::AGGREGATE_CREATED_AT],
        );
    }

    /**
     * @param SnapshotInterface $snapshot
     * @return array<string, mixed>
     */
    public function toArray(SnapshotInterface $snapshot): array
    {
        return [
            SnapshotInterface::AGGREGATE_TYPE => $snapshot->getAggregateType(),
            SnapshotInterface::AGGREGATE_ID => $snapshot->getAggregateId(),
            SnapshotInterface::AGGREGATE_ROOT => $snapshot->getAggregateRoot(),
            SnapshotInterface::AGGREGATE_VERSION => $snapshot->getLastVersion(),
            SnapshotInterface::AGGREGATE_CREATED_AT => $snapshot->getCreatedAt()->format('Y-m-d H:i:s')
        ];
    }
}
