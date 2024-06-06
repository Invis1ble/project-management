<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Infrastructure\Domain\Serializer\Normalizer\IdNormalizer;

use Invis1ble\ProjectManagement\Tests\Shared\Infrastructure\Domain\Serializer\SerializerAwareTestCase;

class IdNormalizerTest extends SerializerAwareTestCase
{
    public function testNormalizeAndDenormalize(): void
    {
        $serializer = static::getSerializer();

        $id1 = new TestId(1);
        $id2 = new TestId(2);

        $dto = new IdAwareDto($id1, $id2);

        $normalized = $serializer->normalize($dto);
        $denormalized = $serializer->denormalize($normalized, IdAwareDto::class);

        $this->assertInstanceOf(IdAwareDto::class, $denormalized);
        $this->assertObjectEquals($id1, $denormalized->abstract);
        $this->assertObjectEquals($id2, $denormalized->concrete);
    }
}
