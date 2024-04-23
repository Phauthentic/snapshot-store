<?php

declare(strict_types=1);

namespace Phauthentic\SnapshotStore\Test\Store;

use Phauthentic\SnapshotStore\Serializer\SerializeSerializer;
use Phauthentic\SnapshotStore\Store\FileSnapshotStore;
use Phauthentic\SnapshotStore\Store\SnapshotStoreInterface;

/**
 *
 */
class FileStoreTest extends AbstractStoreTestCase
{
    protected function createSnapshotStore(): SnapshotStoreInterface
    {
        return new FileSnapshotStore(new SerializeSerializer());
    }
}
