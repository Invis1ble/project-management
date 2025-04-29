<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\Serializer;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

abstract class AbstractStringableValueObjectNormalizer extends AbstractValueObjectNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function denormalize(
        mixed $data,
        string $type,
        ?string $format = null,
        array $context = [],
    ): object {
        return $this->createValueObject($data, $type);
    }

    public function normalize(
        mixed $object,
        ?string $format = null,
        array $context = [],
    ): string {
        return $this->stringify($object);
    }

    protected function getDenormalizationFactoryMethod(): string
    {
        return 'from';
    }

    protected function createValueObject(string $value, string $type): object
    {
        return call_user_func(
            [$type, $this->getDenormalizationFactoryMethod()],
            $value,
        );
    }

    protected function stringify($value): string
    {
        return (string) $value;
    }
}
