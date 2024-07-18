<?php

declare(strict_types=1);

namespace Phauthentic\SnapshotStore\Test\Store;

use DateTimeImmutable;
use Phauthentic\SnapshotStore\Snapshot;
use Phauthentic\SnapshotStore\Store\NullSnapshotStore;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use stdClass;

/**
 *
 */
class NullStoreTest extends TestCase
{
    public function testNullSnapshotStore(): void
    {
        $aggregateId = Uuid::uuid4()->toString();
        $aggregateType = 'User';
        $aggregateRoot = new stdClass();
        $aggregateVersion = 1;
        $createdAt = new DateTimeImmutable('2022-01-01 12:00:00');

        $snapshot = new Snapshot($aggregateType, $aggregateId, $aggregateRoot, $aggregateVersion, $createdAt);

        $snapshotStore = new NullSnapshotStore();

        $snapshotStore->store($snapshot);
        $this->assertNull($snapshotStore->get($aggregateId));
        $this->assertNull($snapshotStore->get(''));
        $snapshotStore->delete('');
    }
}
