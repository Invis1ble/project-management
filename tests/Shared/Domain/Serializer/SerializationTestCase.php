<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Domain\Serializer;

use Invis1ble\ProjectManagement\Tests\Shared\SerializerAwareTestCase;

/**
 * @template T of object
 */
abstract class SerializationTestCase extends SerializerAwareTestCase
{
    public function testSerializationRoundtrip(): void
    {
        $serializer = $this->getSerializer();

        $object = $this->createObject();
        $serialized = $serializer->serialize($object, 'json');
        $deserialized = $serializer->deserialize($serialized, $object::class, 'json');

        $this->assertTrue($this->objectsEquals($object, $deserialized));
    }

    /**
     * @return T
     */
    abstract protected function createObject(): object;

    /**
     * @param T $object1
     * @param T $object2
     */
    abstract protected function objectsEquals(object $object1, object $object2): bool;
}
