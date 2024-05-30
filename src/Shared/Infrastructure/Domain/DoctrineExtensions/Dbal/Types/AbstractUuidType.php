<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\DoctrineExtensions\Dbal\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\GuidType;
use Invis1ble\ProjectManagement\Shared\Domain\Model\AbstractUuid;
use Symfony\Component\Uid\Uuid;

abstract class AbstractUuidType extends GuidType
{
    public const string NAME = 'abstract_uuid';

    public const string ID_CLASS_NAME = AbstractUuid::class;

    public function convertToPHPValue($value, AbstractPlatform $platform): ?AbstractUuid
    {
        if ($value instanceof AbstractUuid) {
            return $value;
        }

        if (!is_string($value) || '' === $value) {
            return null;
        }

        $idFqcn = $this->idFqcn();

        if (!is_callable([$idFqcn, 'fromString'])) {
            throw new \RuntimeException("Method $idFqcn::fromString() is not callable.");
        }

        try {
            $uuid = $idFqcn::fromString($value);
        } catch (\Throwable) {
            throw ConversionException::conversionFailed($value, static::NAME);
        }

        return $uuid;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (null === $value || '' === $value) {
            return null;
        }

        if ($value instanceof AbstractUuid
            || (
                (is_string($value)
                || (is_object($value) && method_exists($value, '__toString')))
                && Uuid::isValid((string) $value)
            )
        ) {
            return (string) $value;
        }

        throw ConversionException::conversionFailed($value, static::NAME);
    }

    public function getName(): string
    {
        return static::NAME;
    }

    /**
     * @return string[]
     */
    public function getMappedDatabaseTypes(AbstractPlatform $platform): array
    {
        return [static::NAME];
    }

    protected function idFqcn(): string
    {
        return static::ID_CLASS_NAME;
    }
}
