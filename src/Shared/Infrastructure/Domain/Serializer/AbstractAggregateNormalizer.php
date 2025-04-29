<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\Serializer;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

abstract class AbstractAggregateNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function supportsDenormalization(
        mixed $data,
        string $type,
        ?string $format = null,
        array $context = [],
    ): false {
        return false;
    }

    public function denormalize(
        mixed $data,
        string $type,
        ?string $format = null,
        array $context = [],
    ): null {
        return null;
    }

    public function supportsNormalization(
        mixed $data,
        ?string $format = null,
        array $context = [],
    ): bool {
        return $data instanceof ($this->supportedType());
    }

    /**
     * @param object $aggregate
     */
    public function normalize(
        mixed $aggregate,
        ?string $format = null,
        array $context = [],
    ): array {
        $properties = [];

        foreach ($this->normalizableProperties() as $propertyName) {
            $properties[$propertyName] = $aggregate->{$propertyName}();
        }

        return $this->normalizer->normalize($properties);
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            $this->supportedType() => true,
        ];
    }

    abstract protected function normalizableProperties(): array;

    abstract protected function supportedType(): string;
}
