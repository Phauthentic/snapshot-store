<?php

declare(strict_types=1);

namespace Phauthentic\SnapshotStore\Test\Store;

use DateTimeImmutable;
use Phauthentic\SnapshotStore\Snapshot;
use Phauthentic\SnapshotStore\SnapshotInterface;
use Phauthentic\SnapshotStore\Store\SnapshotStoreInterface;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use stdClass;

/**
 *
 */
abstract class AbstractStoreTestCase extends TestCase
{
    public function getSnapshot(): SnapshotInterface
    {
        $aggregateId = Uuid::uuid4()->toString();
        $aggregateType = 'User';
        $aggregateRoot = new stdClass();
        $aggregateVersion = 1;
        $createdAt = new DateTimeImmutable('2022-01-01 12:00:00');

        return new Snapshot($aggregateType, $aggregateId, $aggregateRoot, $aggregateVersion, $createdAt);
    }

    abstract protected function createSnapshotStore(): SnapshotStoreInterface;

    public function testStoreRetrieveAndDeleteSnapshot(): void
    {
        $snapshotStore = $this->createSnapshotStore();
        $snapshot = $this->getSnapshot();

        $snapshotStore->store($snapshot);
        $retrievedSnapshot = $snapshotStore->get($snapshot->getAggregateId());

        $this->assertInstanceOf(SnapshotInterface::class, $retrievedSnapshot);
        $this->assertEquals($snapshot->getAggregateType(), $retrievedSnapshot->getAggregateType());
        $this->assertEquals($snapshot->getAggregateId(), $retrievedSnapshot->getAggregateId());
        $this->assertEquals($snapshot->getAggregateRoot(), $retrievedSnapshot->getAggregateRoot());
        $this->assertEquals($snapshot->getLastVersion(), $retrievedSnapshot->getLastVersion());
        $this->assertEquals($snapshot->getCreatedAt(), $retrievedSnapshot->getCreatedAt());

        $snapshotStore->delete($snapshot->getAggregateId());

        $retrievedSnapshot = $snapshotStore->get($snapshot->getAggregateId());
        $this->assertNull($retrievedSnapshot);
    }
}
