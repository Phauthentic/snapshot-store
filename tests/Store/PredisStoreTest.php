<?php

declare(strict_types=1);

namespace Phauthentic\SnapshotStore\Test\Store;

use Phauthentic\SnapshotStore\Serializer\SerializeSerializer;
use Phauthentic\SnapshotStore\SnapshotFactory;
use Phauthentic\SnapshotStore\Store\PredisSnapshotStore as StorePredisStore;
use Phauthentic\SnapshotStore\Store\SnapshotStoreInterface;
use Predis\Client;
use Predis\ClientInterface;

/**
 *
 */
class PredisStoreTest extends AbstractStoreTestCase
{
    protected function getPredisClient(): ClientInterface
    {
        return new Client([
            'scheme' => getenv('REDIS_SCHEME') ?: 'tcp',
            'host'   => getenv('REDIS_HOST') ?: '127.0.0.1',
            'port'   => getenv('REDIS_PORT') ?: 6379,
        ]);
    }

    protected function createSnapshotStore(): SnapshotStoreInterface
    {
        return new StorePredisStore(
            redis: $this->getPredisClient(),
            snapshotFactory: new SnapshotFactory(),
            serializer: new SerializeSerializer()
        );
    }

    protected function skipIfPredisIsNotInstalled(): bool
    {
        if (!class_exists('\Predis\Client')) {
            $this->markTestSkipped('Predis is not installed.');
        }

        return false;
    }

    public function testStoreRetrieveAndDeleteSnapshot(): void
    {
        if ($this->skipIfPredisIsNotInstalled()) {
            return;
        }

        parent::testStoreRetrieveAndDeleteSnapshot();
    }
}
