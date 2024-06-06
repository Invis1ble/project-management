<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\Serializer;

use Invis1ble\ProjectManagement\Shared\Domain\Model\AbstractId;

final class IdNormalizer extends AbstractStringableValueObjectNormalizer
{
    protected function getSupportedType(): string
    {
        return AbstractId::class;
    }
}
