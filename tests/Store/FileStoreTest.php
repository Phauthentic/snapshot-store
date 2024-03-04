<?php

declare(strict_types=1);

namespace Phauthentic\SnapshotStore\Test\Store;

use Phauthentic\SnapshotStore\Serializer\SerializeSerializer;
use Phauthentic\SnapshotStore\Store\FileStore;
use Phauthentic\SnapshotStore\Store\SnapshotStoreInterface;

class FileStoreTest extends AbstractStoreTestCase
{
    protected function createSnapshotStore(): SnapshotStoreInterface
    {
        return new FileStore(new SerializeSerializer());
    }
}
