<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\Serializer;

use Invis1ble\ProjectManagement\Shared\Domain\Model\String_;

final class StringNormalizer extends AbstractStringableValueObjectNormalizer
{
    protected function getSupportedType(): string
    {
        return String_::class;
    }

    protected function getDenormalizationFactoryMethod(): string
    {
        return 'fromString';
    }
}
