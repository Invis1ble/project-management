<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\Serializer;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\UidNormalizer as SymfonyUidNormalizer;
use Symfony\Component\Uid\AbstractUid;

final readonly class UidNormalizer implements NormalizerInterface, DenormalizerInterface
{
    private const string KEY_TYPE = 'type';

    private const string KEY_VALUE = 'value';

    public function __construct(private SymfonyUidNormalizer $uidNormalizer)
    {
    }

    public function denormalize(
        mixed $data,
        string $type,
        ?string $format = null,
        array $context = [],
    ): AbstractUid {
        return $this->uidNormalizer->denormalize($data[self::KEY_VALUE], $data[self::KEY_TYPE], $format, $context);
    }

    public function supportsDenormalization(
        mixed $data,
        string $type,
        ?string $format = null,
        array $context = [],
    ): bool {
        return is_a($type, AbstractUid::class, true);
    }

    public function normalize(
        mixed $object,
        ?string $format = null,
        array $context = [],
    ): array {
        return [
            self::KEY_TYPE => $object::class,
            self::KEY_VALUE => $this->uidNormalizer->normalize($object, $format, $context),
        ];
    }

    public function supportsNormalization(
        mixed $data,
        ?string $format = null,
        array $context = [],
    ): bool {
        return $this->uidNormalizer->supportsNormalization($data, $format, $context);
    }

    public function getSupportedTypes(?string $format): array
    {
        return $this->uidNormalizer->getSupportedTypes($format);
    }
}
