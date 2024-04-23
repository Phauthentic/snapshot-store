<?php

declare(strict_types=1);

namespace Phauthentic\SnapshotStore;

use DateTimeImmutable;
use Phauthentic\SnapshotStore\Exception\SnapshotStoreException;
use Phauthentic\SnapshotStore\Serializer\SerializerInterface;

/**
 * SnapshotTransformer
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class SnapshotTransformer implements SnapshotTransformerInterface
{
    protected const DATE_FORMAT = 'Y-m-d H:i:s';

    public function __construct(
        protected SerializerInterface $serializer
    ) {
    }

    /**
     * @var array<string, string>
     */
    protected array $fieldMap = [
        SnapshotInterface::AGGREGATE_TYPE => 'aggregate_type',
        SnapshotInterface::AGGREGATE_ID => 'aggregate_id',
        SnapshotInterface::AGGREGATE_VERSION => 'aggregate_version',
        SnapshotInterface::AGGREGATE_ROOT => 'aggregate_root',
        SnapshotInterface::AGGREGATE_CREATED_AT => 'created_at'
    ];

    /**
     * @param SnapshotInterface $snapshot
     * @return array<string, mixed>
     */
    public function snapshotToArray(SnapshotInterface $snapshot): array
    {
        return [
            $this->fieldMap[SnapshotInterface::AGGREGATE_TYPE] => $snapshot->getAggregateType(),
            $this->fieldMap[SnapshotInterface::AGGREGATE_ID] => $snapshot->getAggregateId(),
            $this->fieldMap[SnapshotInterface::AGGREGATE_VERSION] => $snapshot->getLastVersion(),
            $this->fieldMap[SnapshotInterface::AGGREGATE_ROOT] => $this->serializer->serialize($snapshot->getAggregateRoot()),
            $this->fieldMap[SnapshotInterface::AGGREGATE_CREATED_AT] => $snapshot->getCreatedAt()->format(static::DATE_FORMAT)
        ];
    }

    /**
     * @param array<string, mixed> $array
     * @return void
     */
    protected function assertArrayKeys(array $array): void
    {
        /** @var array<int, string> $fields */
        $fields = array_values($this->fieldMap);
        foreach ($fields as $field) {
            assert(isset($array[$field]), sprintf('The array is missing the key %s.', $field));
        }
    }

    /**
     * @param array<string, mixed> $data
     * @return SnapshotInterface
     */
    public function arrayToSnapshot(array $data): SnapshotInterface
    {
        $this->assertArrayKeys($data);

        $createdAt = $data[$this->fieldMap[SnapshotInterface::AGGREGATE_CREATED_AT]];
        $createdAtDateTime = DateTimeImmutable::createFromFormat(static::DATE_FORMAT, $createdAt);

        if ($createdAtDateTime === false) {
            throw new SnapshotStoreException('Failed to create DateTimeImmutable from the provided date.');
        }

        return new Snapshot(
            aggregateType: (string)$data[$this->fieldMap[SnapshotInterface::AGGREGATE_TYPE]],
            aggregateId: (string)$data[$this->fieldMap[SnapshotInterface::AGGREGATE_ID]],
            aggregateRoot: $this->serializer->unserialize($this->fieldMap[SnapshotInterface::AGGREGATE_ROOT]),
            lastVersion: (int)$data[$this->fieldMap[SnapshotInterface::AGGREGATE_VERSION]],
            createdAt: $createdAtDateTime
        );
    }
}
