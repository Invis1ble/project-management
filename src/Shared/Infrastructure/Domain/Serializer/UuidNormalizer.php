<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\Serializer;

use Invis1ble\ProjectManagement\Shared\Domain\Model\AbstractUuid;

final class UuidNormalizer extends AbstractStringableValueObjectNormalizer
{
    protected function getSupportedType(): string
    {
        return AbstractUuid::class;
    }

    protected function getDenormalizationFactoryMethod(): string
    {
        return 'fromString';
    }
}
