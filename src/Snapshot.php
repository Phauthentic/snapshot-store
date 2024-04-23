<?php

declare(strict_types=1);

namespace Phauthentic\SnapshotStore;

use Phauthentic\SnapshotStore\Exception\AssertionException;
use DateTimeImmutable;

/**
 * Snapshot
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class Snapshot implements SnapshotInterface
{
    /**
     * @var string
     */
    private string $aggregateType;

    /**
     * @var string
     */
    private string $aggregateId;

    /**
     * @var object
     */
    private object $aggregateRoot;

    /**
     * @var int
     */
    private int $lastVersion;

    /**
     * @var DateTimeImmutable
     */
    private DateTimeImmutable $createdAt;

    /**
     * Constructor
     *
     * @param string             $aggregateType Aggregate Type
     * @param string             $aggregateId   Aggregate Id
     * @param object             $aggregateRoot Aggregate Root
     * @param int                $lastVersion   Last Version
     * @param \DateTimeImmutable $createdAt     Created at
     */
    public function __construct(
        string $aggregateType,
        string $aggregateId,
        object $aggregateRoot,
        int $lastVersion,
        DateTimeImmutable $createdAt
    ) {
        $this->assertNotEmptyString($aggregateType);

        $this->aggregateType = $aggregateType;
        $this->aggregateId = $aggregateId;
        $this->aggregateRoot = $aggregateRoot;
        $this->lastVersion = $lastVersion;
        $this->createdAt = $createdAt;
    }

    protected function assertNotEmptyString(mixed $string): void
    {
        if (empty($string) || !is_string($string)) {
            throw AssertionException::notEmptyString($string);
        }
    }

    /**
     * @inheritDoc
     */
    public function getAggregateType(): string
    {
        return $this->aggregateType;
    }

    /**
     * @inheritDoc
     */
    public function getAggregateId(): string
    {
        return $this->aggregateId;
    }

    /**
     * @inheritDoc
     */
    public function getAggregateRoot(): object
    {
        return $this->aggregateRoot;
    }

    /**
     * @inheritDoc
     */
    public function getLastVersion(): int
    {
        return $this->lastVersion;
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
