<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Infrastructure\Domain\Serializer\Normalizer\ListNormalizer;

use Invis1ble\ProjectManagement\Tests\Shared\Infrastructure\Domain\Serializer\Normalizer\IdNormalizer\TestId;
use Invis1ble\ProjectManagement\Tests\Shared\SerializerAwareTestCase;

class ListNormalizerTest extends SerializerAwareTestCase
{
    public function testNormalizeAndDenormalize(): void
    {
        $serializer = static::getSerializer();

        $list = new TestList(TestId::from(1), TestId::from(2));
        $dto = new ListAwareDto($list);

        $normalized = $serializer->normalize($dto);
        $denormalized = $serializer->denormalize($normalized, ListAwareDto::class);

        $this->assertInstanceOf(ListAwareDto::class, $denormalized);
        $this->assertObjectEquals($list, $denormalized->list);
    }
}
