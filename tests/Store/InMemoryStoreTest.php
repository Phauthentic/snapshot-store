<?php

declare(strict_types=1);

namespace Phauthentic\SnapshotStore\Test\Store;

use Phauthentic\SnapshotStore\Serializer\SerializeSerializer;
use Phauthentic\SnapshotStore\Store\InMemorySnapshotStore;
use Phauthentic\SnapshotStore\Store\SnapshotStoreInterface;

/**
 *
 */
class InMemoryStoreTest extends AbstractStoreTestCase
{
    protected function createSnapshotStore(): SnapshotStoreInterface
    {
        return new InMemorySnapshotStore(new SerializeSerializer());
    }
}
