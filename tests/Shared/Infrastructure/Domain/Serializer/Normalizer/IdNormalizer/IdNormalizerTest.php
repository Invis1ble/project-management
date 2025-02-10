<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Infrastructure\Domain\Serializer\Normalizer\IdNormalizer;

use Invis1ble\ProjectManagement\Tests\Shared\Domain\SerializerAwareTestCase;

class IdNormalizerTest extends SerializerAwareTestCase
{
    public function testNormalizeAndDenormalize(): void
    {
        $serializer = static::getSerializer();

        $id = new TestId(42);
        $dto = new IdAwareDto($id);

        $normalized = $serializer->normalize($dto);
        $denormalized = $serializer->denormalize($normalized, IdAwareDto::class);

        $this->assertInstanceOf(IdAwareDto::class, $denormalized);
        $this->assertObjectEquals($id, $denormalized->id);
    }
}
