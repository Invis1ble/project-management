<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\Serializer;

use Invis1ble\ProjectManagement\Shared\Domain\Model\AbstractList;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

abstract class AbstractListNormalizer implements NormalizerInterface, DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

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
    ): false {
        return false;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            $this->getSupportedType() => true,
        ];
    }

    public function denormalize(
        mixed $data,
        string $type,
        ?string $format = null,
        array $context = [],
    ): AbstractList {
        return new $type(...array_map(
            fn (mixed $element): mixed => $this->denormalizer->denormalize($element, $this->getElementType($element)),
            $data,
        ));
    }

    public function normalize(
        mixed $data,
        ?string $format = null,
        array $context = [],
    ): null {
        return null;
    }

    abstract protected function getSupportedType(): string;

    abstract protected function getElementType(mixed $data): string;
}
