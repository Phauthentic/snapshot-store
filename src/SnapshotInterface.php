<?php

declare(strict_types=1);

namespace Phauthentic\SnapshotStore;

use DateTimeImmutable;

/**
 * Snapshot Interface
 */
interface SnapshotInterface
{
    public const AGGREGATE_TYPE = 'aggregateType';
    public const AGGREGATE_ID = 'aggregateId';
    public const AGGREGATE_VERSION = 'aggregateVersion';
    public const AGGREGATE_ROOT = 'aggregateRoot';
    public const AGGREGATE_CREATED_AT = 'aggregateCreatedAt';

    /**
     * Gets the Aggregate Type
     *
     * @return string
     */
    public function getAggregateType(): string;

    /**
     * Gets the aggregate UUID as string
     *
     * @return string
     */
    public function getAggregateId(): string;

    /**
     * Gets the aggregate root object
     *
     * @return object
     */
    public function getAggregateRoot(): mixed;

    /**
     * Gets the latest version
     *
     * @return int
     */
    public function getLastVersion(): int;

    /**
     * Gets the date the snapshot was created
     *
     * @return \DateTimeImmutable
     */
    public function getCreatedAt(): DateTimeImmutable;
}
