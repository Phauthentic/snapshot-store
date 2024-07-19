<?php

declare(strict_types=1);

namespace Phauthentic\SnapshotStore\Store;

use Phauthentic\SnapshotStore\Serializer\SerializerInterface;
use Phauthentic\SnapshotStore\Serializer\SerializeSerializer;
use Phauthentic\SnapshotStore\Snapshot;
use Phauthentic\SnapshotStore\SnapshotInterface;
use DateTimeImmutable;
use PDO;
use PDOException;
use PDOStatement;
use Phauthentic\SnapshotStore\Exception\SnapshotStoreException;

/**
 * PDO SQL based Snapshot Store
 *
 * Saves your aggregate state snapshot in a SQL database.
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class PdoSqlSnapshotStore implements SnapshotStoreInterface
{
    /**
     * PDO Instance
     *
     * @var \PDO
     */
    protected PDO $pdo;

    /**
     * Serializer
     *
     * @var \Phauthentic\SnapshotStore\Serializer\SerializerInterface
     */
    protected \Phauthentic\SnapshotStore\Serializer\SerializerInterface $serializer;

    /**
     * Table to store the snapshots in
     *
     * @var string
     */
    protected string $table = 'event_store_snapshots';

    /**
     * Constructor
     *
     * @param \PDO                                                                  $pdo
     * @param \Phauthentic\SnapshotStore\Serializer\SerializerInterface|null $serializer Serializer
     * @param string|null                                                           $table      Table to use
     */
    public function __construct(
        PDO $pdo,
        ?SerializerInterface $serializer = null,
        ?string $table = null
    ) {
        $this->pdo = $pdo;
        $this->serializer = $serializer ?? new SerializeSerializer();
        $this->table = $table ?? 'event_store_snapshots';
    }

    /**
     * Checks for PDO Errors
     *
     * @param  \PDOStatement $statement Statement
     * @return void
     */
    protected function pdoErrorCheck(PDOStatement $statement)
    {
        if ($statement->errorCode() !== '00000') {
            $errorInfo = $statement->errorInfo();

            throw new PDOException($errorInfo[2], $errorInfo[1]);
        }
    }

    /**
     * Stores an aggregate snapshot
     *
     * @param SnapshotInterface $snapshot Snapshot
     * @return void
     */
    public function store(SnapshotInterface $snapshot): void
    {
        $data = $this->mapSnapshotToArray($snapshot);

        $sql = "INSERT INTO $this->table (`aggregate_type`, `aggregate_id`, `aggregate_version`, `aggregate_root`, `created_at`) "
             . "VALUES (:aggregate_type, :aggregate_id, :aggregate_version, :aggregate_root, :created_at)";

        $statement = $this->pdo->prepare($sql);
        $statement->execute($data);

        $this->pdoErrorCheck($statement);
    }

    /**
     * @param SnapshotInterface $snapshot
     * @return array<string, mixed>
     */
    protected function mapSnapshotToArray(SnapshotInterface $snapshot): array
    {
        return [
            'aggregate_type' => $snapshot->getAggregateType(),
            'aggregate_id' => $snapshot->getAggregateId(),
            'aggregate_version' => $snapshot->getLastVersion(),
            'aggregate_root' => $this->serializer->serialize($snapshot->getAggregateRoot()),
            'created_at' => $snapshot->getCreatedAt()->format('Y-m-d H:i:s')
        ];
    }

    /**
     * Gets an aggregate snapshot if one exist
     *
     * @param  string $aggregateId Aggregate Id
     * @return null|SnapshotInterface
     */
    public function get(string $aggregateId): ?SnapshotInterface
    {
        $sql = "SELECT * FROM {$this->table} "
             . "WHERE aggregate_id = :aggregate_id "
             . "ORDER BY aggregate_version";

        $statement = $this->pdo->prepare($sql);
        $statement->execute(
            [
            'aggregate_id' => $aggregateId,
            ]
        );

        $this->pdoErrorCheck($statement);
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if ($result === false) {
            return null;
        }

        return $this->toSnapshot($result);
    }

    /**
     * Turns the data array from PDO into a snapshot DTO
     *
     * @param  array<string, mixed> $data Data
     * @return SnapshotInterface
     */
    protected function toSnapshot(array $data): SnapshotInterface
    {
        $createdAt = $data['created_at'];
        $createdAtDateTime = DateTimeImmutable::createFromFormat(static::DATE_FORMAT, $createdAt);

        if ($createdAtDateTime === false) {
            throw new SnapshotStoreException('Failed to create DateTimeImmutable from the provided date.');
        }

        return new Snapshot(
            aggregateType: $data['aggregate_type'],
            aggregateId: $data['aggregate_id'],
            aggregateRoot: $this->serializer->unserialize($data['aggregate_root']),
            lastVersion: (int)$data['aggregate_version'],
            createdAt: $createdAtDateTime
        );
    }

    /**
     * @inheritDoc
     */
    public function delete(string $aggregateId): void
    {
        $sql = "DELETE FROM {$this->table} "
             . "WHERE aggregate_id = :aggregateId";

        $statement = $this->pdo->prepare($sql);
        $statement->execute([
            'aggregateId' => $aggregateId,
        ]);

        $this->pdoErrorCheck($statement);
    }
}
