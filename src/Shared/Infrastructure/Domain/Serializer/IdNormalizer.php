<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\Serializer;

use Invis1ble\ProjectManagement\Shared\Domain\Model\AbstractId;

final class IdNormalizer extends AbstractValueObjectNormalizer
{
    public function denormalize(
        mixed $data,
        string $type,
        ?string $format = null,
        array $context = [],
    ): AbstractId {
        if (!is_callable([$type, 'from'])) {
            throw new \InvalidArgumentException("Unsupported type $type");
        }

        return $type::from($data);
    }

    public function normalize(
        mixed $data,
        ?string $format = null,
        array $context = [],
    ): int {
        return $data->value();
    }

    protected function getSupportedType(): string
    {
        return AbstractId::class;
    }
}
