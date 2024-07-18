<?php

declare(strict_types=1);

namespace Phauthentic\SnapshotStore\Test;

use DateTimeImmutable;
use Phauthentic\SnapshotStore\Snapshot;
use Phauthentic\SnapshotStore\Exception\AssertionException;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 *
 */
final class SnapshotTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $aggregateType = 'TestAggregate';
        $aggregateId = '1234';
        $aggregateRoot = new stdClass();
        $lastVersion = 1;
        $createdAt = new DateTimeImmutable();

        $snapshot = new Snapshot($aggregateType, $aggregateId, $aggregateRoot, $lastVersion, $createdAt);

        $this->assertSame($aggregateType, $snapshot->getAggregateType());
        $this->assertSame($aggregateId, $snapshot->getAggregateId());
        $this->assertSame($aggregateRoot, $snapshot->getAggregateRoot());
        $this->assertSame($lastVersion, $snapshot->getLastVersion());
        $this->assertSame($createdAt, $snapshot->getCreatedAt());
    }

    public function testEmptyAggregateTypeThrowsException(): void
    {
        $this->expectException(AssertionException::class);
        new Snapshot('', '1234', new stdClass(), 1, new DateTimeImmutable());
    }
}
