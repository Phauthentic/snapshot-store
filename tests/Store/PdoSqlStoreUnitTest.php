<?php

declare(strict_types=1);

namespace Phauthentic\SnapshotStore\Test\Store;

use DateTimeImmutable;
use PDO;
use PDOException;
use PDOStatement;
use Phauthentic\SnapshotStore\Exception\SnapshotStoreException;
use Phauthentic\SnapshotStore\Serializer\SerializeSerializer;
use Phauthentic\SnapshotStore\Snapshot;
use Phauthentic\SnapshotStore\SnapshotInterface;
use Phauthentic\SnapshotStore\Store\PdoSqlSnapshotStore;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Unit tests for PdoSqlSnapshotStore that do not require a database connection.
 */
class PdoSqlStoreUnitTest extends TestCase
{
    public function testStoreThrowsPdoExceptionWhenStatementHasError(): void
    {
        $mockStatement = $this->createMock(PDOStatement::class);
        $mockStatement->method('execute')->willReturn(true);
        $mockStatement->method('errorCode')->willReturn('23000');
        $mockStatement->method('errorInfo')->willReturn(['23000', 1062, 'Duplicate entry']);

        $mockPdo = $this->createMock(PDO::class);
        $mockPdo->method('prepare')->willReturn($mockStatement);

        $store = new PdoSqlSnapshotStore($mockPdo, new SerializeSerializer());
        $snapshot = new Snapshot(
            'Test',
            '123',
            new stdClass(),
            1,
            new DateTimeImmutable()
        );

        $this->expectException(PDOException::class);
        $this->expectExceptionMessage('Duplicate entry');

        $store->store($snapshot);
    }

    public function testGetReturnsNullWhenNoResult(): void
    {
        $mockStatement = $this->createMock(PDOStatement::class);
        $mockStatement->method('execute')->willReturn(true);
        $mockStatement->method('errorCode')->willReturn('00000');
        $mockStatement->method('fetch')->willReturn(false);

        $mockPdo = $this->createMock(PDO::class);
        $mockPdo->method('prepare')->willReturn($mockStatement);

        $store = new PdoSqlSnapshotStore($mockPdo, new SerializeSerializer());

        $this->assertNull($store->get('non-existent-id'));
    }

    public function testGetReturnsSnapshotWhenResultExists(): void
    {
        $aggregateRoot = new stdClass();
        $mockStatement = $this->createMock(PDOStatement::class);
        $mockStatement->method('execute')->willReturn(true);
        $mockStatement->method('errorCode')->willReturn('00000');
        $mockStatement->method('fetch')->willReturn([
            'aggregate_type' => 'Test',
            'aggregate_id' => '123',
            'aggregate_version' => 1,
            'aggregate_root' => serialize($aggregateRoot),
            'created_at' => '2024-01-15 12:00:00',
        ]);

        $mockPdo = $this->createMock(PDO::class);
        $mockPdo->method('prepare')->willReturn($mockStatement);

        $store = new PdoSqlSnapshotStore($mockPdo, new SerializeSerializer());
        $snapshot = $store->get('123');

        $this->assertInstanceOf(SnapshotInterface::class, $snapshot);
        $this->assertSame('Test', $snapshot->getAggregateType());
        $this->assertSame('123', $snapshot->getAggregateId());
        $this->assertEquals($aggregateRoot, $snapshot->getAggregateRoot());
        $this->assertSame(1, $snapshot->getLastVersion());
    }

    public function testGetThrowsWhenStoredDateIsInvalid(): void
    {
        $mockStatement = $this->createMock(PDOStatement::class);
        $mockStatement->method('execute')->willReturn(true);
        $mockStatement->method('errorCode')->willReturn('00000');
        $mockStatement->method('fetch')->willReturn([
            'aggregate_type' => 'Test',
            'aggregate_id' => '123',
            'aggregate_version' => 1,
            'aggregate_root' => serialize(new stdClass()),
            'created_at' => 'invalid-date',
        ]);

        $mockPdo = $this->createMock(PDO::class);
        $mockPdo->method('prepare')->willReturn($mockStatement);

        $store = new PdoSqlSnapshotStore($mockPdo, new SerializeSerializer());

        $this->expectException(SnapshotStoreException::class);
        $this->expectExceptionMessage('Failed to create DateTimeImmutable from the provided date.');

        $store->get('123');
    }

    public function testDeleteExecutesSuccessfully(): void
    {
        $mockStatement = $this->createMock(PDOStatement::class);
        $mockStatement->method('execute')->willReturn(true);
        $mockStatement->method('errorCode')->willReturn('00000');

        $mockPdo = $this->createMock(PDO::class);
        $mockPdo->method('prepare')->willReturn($mockStatement);

        $store = new PdoSqlSnapshotStore($mockPdo, new SerializeSerializer());
        $store->delete('123');

        $this->addToAssertionCount(1);
    }
}
