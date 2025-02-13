<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Infrastructure\Domain\Serializer\Normalizer\StringNormalizer;

use Invis1ble\ProjectManagement\Tests\Shared\SerializerAwareTestCase;

class StringNormalizerTest extends SerializerAwareTestCase
{
    public function testNormalizeAndDenormalize(): void
    {
        $serializer = static::getSerializer();

        $string = new TestString('Lorem ipsum');
        $dto = new StringAwareDto($string);

        $normalized = $serializer->normalize($dto);
        $denormalized = $serializer->denormalize($normalized, StringAwareDto::class);

        $this->assertInstanceOf(StringAwareDto::class, $denormalized);
        $this->assertObjectEquals($string, $denormalized->string);
    }
}
