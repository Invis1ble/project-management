<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Serializer;

use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

final class UriNormalizer extends AbstractNormalizer
{
    public function __construct(
        private readonly UriFactoryInterface $uriFactory,
        ?ClassMetadataFactoryInterface $classMetadataFactory = null,
        ?NameConverterInterface $nameConverter = null,
        array $defaultContext = [],
    ) {
        parent::__construct($classMetadataFactory, $nameConverter, $defaultContext);
    }

    public function normalize(
        mixed $object,
        ?string $format = null,
        array $context = [],
    ): string {
        return (string) $object;
    }

    public function supportsNormalization(
        mixed $data,
        ?string $format = null,
        array $context = [],
    ): bool {
        return $data instanceof Uri;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            UriInterface::class => true,
        ];
    }

    public function denormalize(
        mixed $data,
        string $type,
        ?string $format = null,
        array $context = [],
    ): Uri {
        return $this->uriFactory->createUri((string) $data);
    }

    public function supportsDenormalization(
        mixed $data,
        string $type,
        ?string $format = null,
        array $context = [],
    ): bool {
        return UriInterface::class === $type;
    }
}
