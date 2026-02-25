<?php

declare(strict_types=1);

namespace Phauthentic\SnapshotStore\Test\Store;

use Phauthentic\SnapshotStore\Exception\SnapshotStoreException;
use Phauthentic\SnapshotStore\Serializer\SerializeSerializer;
use Phauthentic\SnapshotStore\SnapshotInterface;
use Phauthentic\SnapshotStore\Store\FileSnapshotStore;
use Phauthentic\SnapshotStore\Store\SnapshotStoreInterface;
use Ramsey\Uuid\Uuid;

/**
 *
 */
class FileStoreTest extends AbstractStoreTestCase
{
    protected function createSnapshotStore(): SnapshotStoreInterface
    {
        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'snapshot-store-test-' . uniqid() . DIRECTORY_SEPARATOR;
        mkdir($path, 0755, true);

        return new FileSnapshotStore(
            new SerializeSerializer(),
            $path
        );
    }

    public function testDeleteNonExistentFileDoesNotThrow(): void
    {
        $store = $this->createSnapshotStore();
        $nonExistentId = Uuid::uuid4()->toString();

        $store->delete($nonExistentId);

        $this->assertNull($store->get($nonExistentId));
    }

    public function testGetThrowsWhenFileCannotBeRead(): void
    {
        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'snapshot-store-read-test-' . uniqid() . DIRECTORY_SEPARATOR;
        mkdir($path, 0755, true);

        $store = new FileSnapshotStore(new SerializeSerializer(), $path);
        $snapshot = $this->getSnapshot();

        $store->store($snapshot);
        $aggregateId = $snapshot->getAggregateId();
        $filePath = $path . $aggregateId;

        chmod($filePath, 0000);

        $this->expectException(SnapshotStoreException::class);
        $this->expectExceptionMessage('Could not read content from file');

        try {
            $store->get($aggregateId);
        } finally {
            chmod($filePath, 0644);
        }
    }

    public function testGetThrowsWhenFileDoesNotExist(): void
    {
        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR
            . 'snapshot-store-assert-test-' . uniqid() . DIRECTORY_SEPARATOR;
        mkdir($path, 0755, true);

        $nonExistentId = Uuid::uuid4()->toString();

        $store = new class (new SerializeSerializer(), $path) extends FileSnapshotStore {
            private ?string $forceExistsFor = null;

            public function setForceExistsFor(string $id): void
            {
                $this->forceExistsFor = $id;
            }

            protected function fileExists(string $file): bool
            {
                if ($this->forceExistsFor !== null && $file === $this->forceExistsFor) {
                    return true;
                }
                return parent::fileExists($file);
            }
        };
        $store->setForceExistsFor($nonExistentId);

        $this->expectException(SnapshotStoreException::class);
        $this->expectExceptionMessage('does not exist');

        $store->get($nonExistentId);
    }

    public function testGetThrowsWhenStoredDateIsInvalid(): void
    {
        $store = $this->createSnapshotStore();
        $snapshot = $this->getSnapshot();
        $store->store($snapshot);

        $path = (new \ReflectionClass($store))->getProperty('path')->getValue($store);
        $filePath = $path . $snapshot->getAggregateId();
        $data = unserialize(file_get_contents($filePath));
        $data[SnapshotInterface::AGGREGATE_CREATED_AT] = 'invalid-date';
        file_put_contents($filePath, serialize($data));

        $this->expectException(SnapshotStoreException::class);
        $this->expectExceptionMessage('Failed to create DateTimeImmutable from the provided date.');

        $store->get($snapshot->getAggregateId());
    }
}
