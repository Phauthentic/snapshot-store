<?php

declare(strict_types=1);

namespace Phauthentic\SnapshotStore\Store;

use Phauthentic\SnapshotStore\Snapshot;
use Redis;
use Phauthentic\SnapshotStore\SnapshotInterface;
use Phauthentic\SnapshotStore\Serializer\SerializerInterface;
use Phauthentic\SnapshotStore\Serializer\SerializeSerializer;
use DateTimeImmutable;
use Phauthentic\SnapshotStore\Exception\SnapshotStoreException;

/**
 * Redis
 *
 * Saves your aggregate state snapshot in Redis.
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class RedisSnapshotStore implements SnapshotStoreInterface
{
    /**
     * Key Prefix
     *
     * @var string
     */
    protected string $keyPrefix = 'aggregate-snapshot:';

    /**
     * Serializer
     *
     * @var \Phauthentic\SnapshotStore\Serializer\SerializerInterface
     */
    protected SerializerInterface $serializer;

    /**
     * @var \Redis
     */

    protected Redis $redis;

    /**
     * Constructor
     *
     * @param \Redis $redis
     * @param \Phauthentic\SnapshotStore\Serializer\SerializerInterface|null $serializer Serializer
     */
    public function __construct(
        Redis $redis,
        ?SerializerInterface $serializer = null
    ) {
        $this->redis = $redis;
        $this->serializer = $serializer ?: new SerializeSerializer();
    }

    /**
     * @inheritDoc
     */
    public function store(SnapshotInterface $snapshot): void
    {
        $data = [
            SnapshotInterface::AGGREGATE_TYPE => $snapshot->getAggregateType(),
            SnapshotInterface::AGGREGATE_ID => $snapshot->getAggregateId(),
            SnapshotInterface::AGGREGATE_VERSION => $snapshot->getLastVersion(),
            SnapshotInterface::AGGREGATE_ROOT => $this->serializer->serialize($snapshot->getAggregateRoot()),
            SnapshotInterface::AGGREGATE_CREATED_AT => $snapshot->getCreatedAt()->format('Y-m-d H:i:s')
        ];

        $this->redis->set(
            $this->keyPrefix . $snapshot->getAggregateId(),
            $this->serializer->serialize($data)
        );
    }

    /**
     * @inheritDoc
     */
    public function get(string $aggregateId): ?SnapshotInterface
    {
        $data = $this->redis->get($this->keyPrefix . $aggregateId);
        $data = $this->serializer->unserialize((string)$data);

        $createdAt = $data[SnapshotInterface::AGGREGATE_CREATED_AT];
        $createdAtDateTime = DateTimeImmutable::createFromFormat(static::DATE_FORMAT, $createdAt);

        if ($createdAtDateTime === false) {
            throw new SnapshotStoreException('Failed to create DateTimeImmutable from the provided date.');
        }

        return new Snapshot(
            aggregateType: $data[SnapshotInterface::AGGREGATE_TYPE],
            aggregateId: $data[SnapshotInterface::AGGREGATE_ID],
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
        $this->redis->del($aggregateId);
    }
}
