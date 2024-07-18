<?php

declare(strict_types=1);

namespace Phauthentic\SnapshotStore\Test;

use DateTimeImmutable;
use Phauthentic\SnapshotStore\Exception\SnapshotStoreException;
use Phauthentic\SnapshotStore\Snapshot;
use Phauthentic\SnapshotStore\SnapshotTransformer;
use Phauthentic\SnapshotStore\Serializer\SerializerInterface;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 *
 */
final class SnapshotTransformerTest extends TestCase
{
    private SerializerInterface $serializer;
    private SnapshotTransformer $transformer;

    protected function setUp(): void
    {
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->transformer = new SnapshotTransformer($this->serializer);
    }

    public function testSnapshotToArray(): void
    {
        $aggregateType = 'TestAggregate';
        $aggregateId = '1234';
        $aggregateRoot = new stdClass();
        $lastVersion = 1;
        $createdAt = new DateTimeImmutable();
        $createdAtFormatted = $createdAt->format('Y-m-d H:i:s');

        $snapshot = new Snapshot($aggregateType, $aggregateId, $aggregateRoot, $lastVersion, $createdAt);

        $this->serializer->method('serialize')->with($aggregateRoot)->willReturn('serialized_aggregate_root');

        $result = $this->transformer->snapshotToArray($snapshot);

        $expected = [
            'aggregate_type' => $aggregateType,
            'aggregate_id' => $aggregateId,
            'aggregate_version' => $lastVersion,
            'aggregate_root' => 'serialized_aggregate_root',
            'created_at' => $createdAtFormatted
        ];

        $this->assertSame($expected, $result);
    }

    public function testArrayToSnapshot(): void
    {
        $aggregateRoot = new stdClass();

        $data = [
            'aggregate_type' => 'TestAggregate',
            'aggregate_id' => '1234',
            'aggregate_version' => 1,
            'aggregate_root' => serialize($aggregateRoot),
            'created_at' => '2024-07-18 12:34:56'
        ];

        $aggregateRoot = new stdClass();
        $createdAt = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2024-07-18 12:34:56');

        $this->serializer->method('unserialize')->with('aggregate_root')->willReturn($aggregateRoot);

        $snapshot = $this->transformer->arrayToSnapshot($data);

        $this->assertSame('TestAggregate', $snapshot->getAggregateType());
        $this->assertSame('1234', $snapshot->getAggregateId());
        $this->assertSame($aggregateRoot, $snapshot->getAggregateRoot());
        $this->assertSame(1, $snapshot->getLastVersion());
        $this->assertEquals($createdAt, $snapshot->getCreatedAt());
    }

    public function testArrayToSnapshotMissingKeyThrowsException(): void
    {
        $data = [
            'aggregate_type' => 'TestAggregate',
            'aggregate_id' => '1234',
            'aggregate_version' => 1,
            'aggregate_root' => 'serialized_aggregate_root',
        ];

        $this->expectException(SnapshotStoreException::class);
        $this->expectExceptionMessage('The array is missing the key created_at.');

        $this->transformer->arrayToSnapshot($data);
    }

    public function testArrayToSnapshotInvalidDateThrowsException(): void
    {
        $data = [
            'aggregate_type' => 'TestAggregate',
            'aggregate_id' => '1234',
            'aggregate_version' => 1,
            'aggregate_root' => 'serialized_aggregate_root',
            'created_at' => 'invalid_date'
        ];

        $this->expectException(SnapshotStoreException::class);
        $this->expectExceptionMessage('Failed to create DateTimeImmutable from the provided date.');

        $this->transformer->arrayToSnapshot($data);
    }
}
