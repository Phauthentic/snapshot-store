<?php

declare(strict_types=1);

namespace Phauthentic\SnapshotStore\Test;

use DateTimeImmutable;
use Phauthentic\SnapshotStore\SnapshotFactory;
use Phauthentic\SnapshotStore\SnapshotFactoryInterface;
use Phauthentic\SnapshotStore\SnapshotInterface;
use Phauthentic\SnapshotStore\Exception\AssertionException;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 *
 */
class SnapshotFactoryTest extends TestCase
{
    protected SnapshotFactoryInterface $snapshotFactory;

    protected function setUp(): void
    {
        $this->snapshotFactory = new SnapshotFactory();
    }

    public function testFromArrayWithValidData(): void
    {
        $dateTime = new DateTimeImmutable('2022-01-01 12:00:00');
        $aggregate = new stdClass();

        $data = [
            SnapshotInterface::AGGREGATE_TYPE => 'user',
            SnapshotInterface::AGGREGATE_ID => '123',
            SnapshotInterface::AGGREGATE_ROOT => $aggregate,
            SnapshotInterface::AGGREGATE_VERSION => 1,
            SnapshotInterface::AGGREGATE_CREATED_AT => $dateTime
        ];

        $snapshot = $this->snapshotFactory->fromArray($data);

        $this->assertInstanceOf(SnapshotInterface::class, $snapshot);
        $this->assertEquals('user', $snapshot->getAggregateType());
        $this->assertEquals('123', $snapshot->getAggregateId());
        $this->assertEquals($aggregate, $snapshot->getAggregateRoot());
        $this->assertEquals(1, $snapshot->getLastVersion());
        $this->assertEquals($dateTime, $snapshot->getCreatedAt());
    }

    public function testFromArrayWithMissingKey(): void
    {
        $data = [
            SnapshotInterface::AGGREGATE_TYPE => 'user',
            SnapshotInterface::AGGREGATE_ID => '123',
            SnapshotInterface::AGGREGATE_ROOT => 'user_123',
            SnapshotInterface::AGGREGATE_VERSION => 1,
            // Missing AGGREGATE_CREATED_AT key
        ];

        $this->expectException(AssertionException::class);
        $this->expectExceptionMessage('The array is missing the `aggregateCreatedAt` key');

        $this->snapshotFactory->fromArray($data);
    }

    public function testToArray(): void
    {
        $dateTime = new DateTimeImmutable('2022-01-01 12:00:00');
        $aggregate = new stdClass();

        $data = [
            SnapshotInterface::AGGREGATE_TYPE => 'user',
            SnapshotInterface::AGGREGATE_ID => '123',
            SnapshotInterface::AGGREGATE_ROOT => $aggregate,
            SnapshotInterface::AGGREGATE_VERSION => 1,
            SnapshotInterface::AGGREGATE_CREATED_AT => $dateTime
        ];

        $snapshot = $this->snapshotFactory->fromArray($data);

        $data[SnapshotInterface::AGGREGATE_CREATED_AT] = '2022-01-01 12:00:00';

        $this->assertSame($data, $this->snapshotFactory->toArray($snapshot));
    }
}
