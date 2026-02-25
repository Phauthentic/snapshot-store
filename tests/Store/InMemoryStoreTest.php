<?php

declare(strict_types=1);

namespace Phauthentic\SnapshotStore\Test\Store;

use Phauthentic\SnapshotStore\Exception\SnapshotStoreException;
use Phauthentic\SnapshotStore\Serializer\SerializeSerializer;
use Phauthentic\SnapshotStore\SnapshotInterface;
use Phauthentic\SnapshotStore\Store\InMemorySnapshotStore;
use Phauthentic\SnapshotStore\Store\SnapshotStoreInterface;
use Ramsey\Uuid\Uuid;
use ReflectionClass;
use stdClass;

/**
 *
 */
class InMemoryStoreTest extends AbstractStoreTestCase
{
    protected function createSnapshotStore(): SnapshotStoreInterface
    {
        return new InMemorySnapshotStore(new SerializeSerializer());
    }

    public function testGetThrowsWhenStoredDateIsInvalid(): void
    {
        $store = new InMemorySnapshotStore(new SerializeSerializer());
        $aggregateId = Uuid::uuid4()->toString();

        $reflection = new ReflectionClass($store);
        $storeProperty = $reflection->getProperty('store');
        $storeProperty->setAccessible(true);
        $storeProperty->setValue($store, [
            $aggregateId => [
                SnapshotInterface::AGGREGATE_TYPE => 'Test',
                SnapshotInterface::AGGREGATE_ID => $aggregateId,
                SnapshotInterface::AGGREGATE_VERSION => 1,
                SnapshotInterface::AGGREGATE_ROOT => serialize(new stdClass()),
                SnapshotInterface::AGGREGATE_CREATED_AT => 'invalid-date-format',
            ],
        ]);

        $this->expectException(SnapshotStoreException::class);
        $this->expectExceptionMessage('Failed to create DateTimeImmutable from the provided date.');

        $store->get($aggregateId);
    }
}
