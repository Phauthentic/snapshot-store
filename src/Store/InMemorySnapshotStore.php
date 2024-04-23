<?php

declare(strict_types=1);

namespace Phauthentic\SnapshotStore\Store;

use DateTimeImmutable;
use Phauthentic\SnapshotStore\Exception\SnapshotStoreException;
use Phauthentic\SnapshotStore\Snapshot;
use Phauthentic\SnapshotStore\Serializer\SerializerInterface;
use Phauthentic\SnapshotStore\Serializer\SerializeSerializer;
use Phauthentic\SnapshotStore\SnapshotInterface;

/**
 * In Memory Store
 *
 * Saves your aggregate state snapshot in memory.
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class InMemorySnapshotStore implements SnapshotStoreInterface
{
    /**
     * Stores the snapshots
     *
     * @var array<mixed, array<string, int|string>>
     */
    protected array $store = [];

    /**
     * Serializer
     *
     * @var \Phauthentic\SnapshotStore\Serializer\SerializerInterface
     */
    protected SerializerInterface $serializer;

    /**
     * Constructor
     *
     * @param \Phauthentic\SnapshotStore\Serializer\SerializerInterface|null $serializer Serializer
     */
    public function __construct(
        ?SerializerInterface $serializer = null
    ) {
        $this->serializer = $serializer ?: new SerializeSerializer();
    }

    /**
     * @inheritDoc
     */
    public function store(SnapshotInterface $snapshot): void
    {
        $this->store[$snapshot->getAggregateId()] = [
            SnapshotInterface::AGGREGATE_TYPE => $snapshot->getAggregateType(),
            SnapshotInterface::AGGREGATE_ID => $snapshot->getAggregateId(),
            SnapshotInterface::AGGREGATE_VERSION => $snapshot->getLastVersion(),
            SnapshotInterface::AGGREGATE_ROOT => $this->serializer->serialize($snapshot->getAggregateRoot()),
            SnapshotInterface::AGGREGATE_CREATED_AT => $snapshot->getCreatedAt()->format('Y-m-d H:i:s')
        ];
    }

    /**
     * @inheritDoc
     */
    public function get(string $aggregateId): ?SnapshotInterface
    {
        if (!isset($this->store[$aggregateId])) {
            return null;
        }

        $data = $this->store[$aggregateId];

        $createdAt = (string)$data[SnapshotInterface::AGGREGATE_CREATED_AT];
        $createdAtDateTime = DateTimeImmutable::createFromFormat(static::DATE_FORMAT, $createdAt);

        if ($createdAtDateTime === false) {
            throw new SnapshotStoreException('Failed to create DateTimeImmutable from the provided date.');
        }

        return new Snapshot(
            aggregateType: (string)$data[SnapshotInterface::AGGREGATE_TYPE],
            aggregateId: (string)$data[SnapshotInterface::AGGREGATE_ID],
            aggregateRoot: $this->serializer->unserialize((string)$data[SnapshotInterface::AGGREGATE_ROOT]),
            lastVersion: (int)$data[SnapshotInterface::AGGREGATE_VERSION],
            createdAt: $createdAtDateTime
        );
    }

    /**
     * @inheritDoc
     */
    public function delete(string $aggregateId): void
    {
        unset($this->store[$aggregateId]);
    }
}
