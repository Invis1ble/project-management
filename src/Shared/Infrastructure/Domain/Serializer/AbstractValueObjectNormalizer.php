<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\Serializer;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

abstract class AbstractValueObjectNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function supportsDenormalization(
        mixed $data,
        string $type,
        ?string $format = null,
        array $context = [],
    ): bool {
        return is_a($type, $this->getSupportedType(), true);
    }

    public function supportsNormalization(
        mixed $data,
        ?string $format = null,
        array $context = [],
    ): bool {
        return $data instanceof ($this->getSupportedType());
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            $this->getSupportedType() => true,
        ];
    }

    abstract protected function getSupportedType(): string;
}
