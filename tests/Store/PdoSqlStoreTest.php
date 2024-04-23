<?php

declare(strict_types=1);

namespace Phauthentic\SnapshotStore\Test\Store;

use PDO;
use Phauthentic\SnapshotStore\Serializer\SerializeSerializer;
use Phauthentic\SnapshotStore\Store\PdoSqlSnapshotStore;
use Phauthentic\SnapshotStore\Store\SnapshotStoreInterface;
use RuntimeException;

/**
 *
 */
class PdoSqlStoreTest extends AbstractStoreTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $pdo = $this->getPdo();
        $query = file_get_contents('./resources/snapshot_store.sql');
        if ($query === false) {
            throw new RuntimeException('Could not read snapshot_store.sql');
        }

        $pdo->query('use test');
        $pdo->query($query);
    }

    protected function getPdo(): PDO
    {
        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $dbname = getenv('DB_DATABASE') ?: 'test';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASSWORD') ?: 'changeme';

        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);

        // Set PDO attributes for error handling and fetch mode
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        return $pdo;
    }

    protected function createSnapshotStore(): SnapshotStoreInterface
    {
        return new PdoSqlSnapshotStore($this->getPdo(), new SerializeSerializer());
    }
}
