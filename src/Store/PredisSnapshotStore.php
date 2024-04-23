<?php

declare(strict_types=1);

namespace Phauthentic\SnapshotStore\Store;

use Phauthentic\SnapshotStore\SnapshotFactory;
use Phauthentic\SnapshotStore\SnapshotFactoryInterface;
use Phauthentic\SnapshotStore\SnapshotInterface;
use Phauthentic\SnapshotStore\Serializer\SerializerInterface;
use Phauthentic\SnapshotStore\Serializer\SerializeSerializer;
use Predis\ClientInterface;

/**
 * Redis
 *
 * Saves your aggregate state snapshot in Redis.
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class PredisSnapshotStore implements SnapshotStoreInterface
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
     * @var \Predis\ClientInterface
     */
    protected ClientInterface $redis;

    protected SnapshotFactoryInterface $snapshotFactory;

    /**
     * Constructor
     *
     * @param \Predis\ClientInterface $redis
     * @param \Phauthentic\SnapshotStore\Serializer\SerializerInterface|null $serializer Serializer
     */
    public function __construct(
        ClientInterface $redis,
        ?SnapshotFactoryInterface $snapshotFactory = null,
        ?SerializerInterface $serializer = null
    ) {
        $this->redis = $redis;
        $this->serializer = $serializer ?: new SerializeSerializer();
        $this->snapshotFactory = $snapshotFactory ?: new SnapshotFactory();
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
            SnapshotInterface::AGGREGATE_ROOT => $snapshot->getAggregateRoot(),
            SnapshotInterface::AGGREGATE_CREATED_AT => $snapshot->getCreatedAt()->format('Y-m-d H:i:s')
        ];

        $this->redis->set(
            $this->createKey($snapshot),
            $this->serializer->serialize($data)
        );
    }

    protected function createKey(SnapshotInterface $snapshot): string
    {
        return $this->keyPrefix . $snapshot->getAggregateId();
    }

    /**
     * @inheritDoc
     */
    public function get(string $aggregateId): ?SnapshotInterface
    {
        $data = $this->redis->get($this->keyPrefix . $aggregateId);
        if ($data === null) {
            return null;
        }

        $data = $this->serializer->unserialize($data);

        return $this->snapshotFactory->fromArray($data);
    }

    /**
     * @inheritDoc
     */
    public function delete(string $aggregateId): void
    {
        $this->redis->del($this->keyPrefix . $aggregateId);
    }
}
