<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Tests\Shared\Infrastructure\Domain\Serializer\Normalizer\ListNormalizer;

use Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\Serializer\AbstractListNormalizer;
use Invis1ble\ProjectManagement\Tests\Shared\Infrastructure\Domain\Serializer\Normalizer\IdNormalizer\TestId;

final class TestListNormalizer extends AbstractListNormalizer
{
    protected function getSupportedType(): string
    {
        return TestList::class;
    }

    protected function getElementType(mixed $data): string
    {
        return TestId::class;
    }
}
