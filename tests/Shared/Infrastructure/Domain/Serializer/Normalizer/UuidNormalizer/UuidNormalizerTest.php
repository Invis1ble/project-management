<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Infrastructure\Domain\Serializer\Normalizer\UuidNormalizer;

use Invis1ble\ProjectManagement\Tests\Shared\Domain\SerializerAwareTestCase;
use Symfony\Component\Uid\Uuid;

class UuidNormalizerTest extends SerializerAwareTestCase
{
    public function testNormalizeAndDenormalize(): void
    {
        $serializer = static::getSerializer();

        $uuid = new TestUuid(Uuid::v4());
        $dto = new UuidAwareDto($uuid);

        $normalized = $serializer->normalize($dto);
        $denormalized = $serializer->denormalize($normalized, UuidAwareDto::class);

        $this->assertInstanceOf(UuidAwareDto::class, $denormalized);
        $this->assertObjectEquals($uuid, $denormalized->uuid);
    }
}
