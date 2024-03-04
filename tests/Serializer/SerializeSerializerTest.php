<?php

declare(strict_types=1);

namespace Phauthentic\SnapshotStore\Test;

use Phauthentic\SnapshotStore\Serializer\SerializeSerializer;
use PHPUnit\Framework\TestCase;
use stdClass;

class SerializeSerializerTest extends TestCase
{
    protected SerializeSerializer $serializer;

    protected function setUp(): void
    {
        $this->serializer = new SerializeSerializer();
    }

    public function testSerializer(): void
    {
        $object = new stdClass();
        $result = $this->serializer->serialize($object);

        $this->assertIsString($result);

        $result = $this->serializer->unserialize($result);
        $this->assertEquals($object, $result);
    }
}
