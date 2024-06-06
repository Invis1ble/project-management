<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Infrastructure\Domain\Serializer\Normalizer\ListNormalizer;

use Invis1ble\ProjectManagement\Tests\Shared\Infrastructure\Domain\Serializer\Normalizer\IdNormalizer\TestId;
use Invis1ble\ProjectManagement\Tests\Shared\Infrastructure\Domain\Serializer\SerializerAwareTestCase;

class ListNormalizerTest extends SerializerAwareTestCase
{
    public function testNormalizeAndDenormalize(): void
    {
        $serializer = static::getSerializer();

        $list1 = new TestList(TestId::from(1), TestId::from(2));
        $list2 = new TestList(TestId::from(3), TestId::from(4));

        $dto = new ListAwareDto($list1, $list2);

        $normalized = $serializer->normalize($dto);
        $denormalized = $serializer->denormalize($normalized, ListAwareDto::class);

        $this->assertInstanceOf(ListAwareDto::class, $denormalized);
        $this->assertObjectEquals($list1, $denormalized->abstract);
        $this->assertObjectEquals($list2, $denormalized->concrete);
    }
}
