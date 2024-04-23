<?php

declare(strict_types=1);

namespace Phauthentic\SnapshotStore\Test\Store;

use Phauthentic\SnapshotStore\Serializer\SerializeSerializer;
use Phauthentic\SnapshotStore\Store\RedisSnapshotStore;
use Phauthentic\SnapshotStore\Store\SnapshotStoreInterface;
use Redis;

/**
 *
 */
class RedisStoreTest extends AbstractStoreTestCase
{
    protected function getRedis(): Redis
    {
        return new Redis([
            'host'   => (string)getenv('REDIS_HOST') ?: '127.0.0.1',
            'port'   => (int)getenv('REDIS_PORT') ?: 6379,
        ]);
    }

    protected function createSnapshotStore(): SnapshotStoreInterface
    {
        return new RedisSnapshotStore(
            redis: $this->getRedis(),
            serializer: new SerializeSerializer()
        );
    }

    protected function skipIfRedisIsNotInstalled(): bool
    {
        if (!class_exists('\Redis')) {
            $this->markTestSkipped('PECL Redis is not installed.');
        }

        return false;
    }

    public function testStoreRetrieveAndDeleteSnapshot(): void
    {
        if ($this->skipIfRedisIsNotInstalled()) {
            return;
        }

        parent::testStoreRetrieveAndDeleteSnapshot();
    }
}
