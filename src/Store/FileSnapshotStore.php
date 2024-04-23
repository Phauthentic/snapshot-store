<?php

declare(strict_types=1);

namespace Phauthentic\SnapshotStore\Store;

use DateTimeImmutable;
use Phauthentic\SnapshotStore\Exception\SnapshotStoreException;
use Phauthentic\SnapshotStore\Snapshot;
use Phauthentic\SnapshotStore\Serializer\SerializerInterface;
use Phauthentic\SnapshotStore\Serializer\SerializeSerializer;
use Phauthentic\SnapshotStore\SnapshotInterface;

/**
 * File System Store
 *
 * Saves your aggregate state snapshot in the file system.
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class FileSnapshotStore implements SnapshotStoreInterface
{
    /**
     * Stores the snapshots
     *
     * @var array<mixed, array<string, int|string>>
     */
    protected array $store = [];

    /**
     * Serializer
     *
     * @var \Phauthentic\SnapshotStore\Serializer\SerializerInterface
     */
    protected SerializerInterface $serializer;

    /**
     * @var string
     */
    protected string $path;

    /**
     * Constructor
     *
     * @param \Phauthentic\SnapshotStore\Serializer\SerializerInterface|null $serializer Serializer
     */
    public function __construct(
        ?SerializerInterface $serializer = null,
        string $path = null
    ) {
        $this->serializer = $serializer ?: new SerializeSerializer();
        $this->path = $path ?? sys_get_temp_dir() . DIRECTORY_SEPARATOR;
    }

    /**
     * @inheritDoc
     */
    public function store(SnapshotInterface $snapshot): void
    {
        $data = [
            SnapshotInterface::AGGREGATE_TYPE => $snapshot->getAggregateType(),
            SnapshotInterface::AGGREGATE_ID => $snapshot->getAggregateId(),
            SnapshotInterface::AGGREGATE_VERSION => $snapshot->getLastVersion(),
            SnapshotInterface::AGGREGATE_ROOT => $snapshot->getAggregateRoot(),
            SnapshotInterface::AGGREGATE_CREATED_AT => $snapshot->getCreatedAt()->format('Y-m-d H:i:s')
        ];

        $this->writeFile(
            $snapshot->getAggregateId(),
            $this->serializer->serialize($data)
        );
    }

    protected function assertFileExists(string $file): void
    {
        assert(file_exists($file), sprintf('File %s does not exist', $file));
    }

    protected function fileExists(string $file): bool
    {
        return file_exists($this->path . $file);
    }

    protected function writeFile(string $file, string $content): void
    {
        file_put_contents($this->path . $file, $content);
    }

    protected function readFile(string $file): string
    {
        $this->assertFileExists($this->path . $file);

        $content = file_get_contents($this->path . $file);

        if ($content === false) {
            throw new SnapshotStoreException(sprintf(
                'Could not read content from file %s',
                $file
            ));
        }

        return $content;
    }

    protected function deleteFile(string $file): void
    {
        if (!$this->fileExists($file)) {
            return;
        }

        unlink($this->path . $file);
    }

    /**
     * @inheritDoc
     */
    public function get(string $aggregateId): ?SnapshotInterface
    {
        if (!$this->fileExists($aggregateId)) {
            return null;
        }

        $data = $this->readFile($aggregateId);
        $data = $this->serializer->unserialize($data);

        $createdAt = $data[SnapshotInterface::AGGREGATE_CREATED_AT];
        $createdAtDateTime = DateTimeImmutable::createFromFormat(static::DATE_FORMAT, $createdAt);

        if ($createdAtDateTime === false) {
            throw new SnapshotStoreException('Failed to create DateTimeImmutable from the provided date.');
        }

        return new Snapshot(
            aggregateType: (string)$data[SnapshotInterface::AGGREGATE_TYPE],
            aggregateId: (string)$data[SnapshotInterface::AGGREGATE_ID],
            aggregateRoot: $data[SnapshotInterface::AGGREGATE_ROOT],
            lastVersion: (int)$data[SnapshotInterface::AGGREGATE_VERSION],
            createdAt: $createdAtDateTime
        );
    }

    /**
     * @inheritDoc
     */
    public function delete(string $aggregateId): void
    {
        $this->deleteFile($aggregateId);
    }
}
