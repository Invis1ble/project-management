<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Infrastructure\Domain\Serializer\Normalizer\StringNormalizer;

use Invis1ble\ProjectManagement\Tests\Shared\Domain\SerializerAwareTestCase;

class StringNormalizerTest extends SerializerAwareTestCase
{
    public function testNormalizeAndDenormalize(): void
    {
        $serializer = static::getSerializer();

        $string1 = new TestString('string 1');
        $string2 = new TestString('string 2');

        $dto = new StringAwareDto($string1, $string2);

        $normalized = $serializer->normalize($dto);
        $denormalized = $serializer->denormalize($normalized, StringAwareDto::class);

        $this->assertInstanceOf(StringAwareDto::class, $denormalized);
        $this->assertObjectEquals($string1, $denormalized->abstract);
        $this->assertObjectEquals($string2, $denormalized->concrete);
    }
}
