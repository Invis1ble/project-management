<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Infrastructure\Domain\DoctrineExtensions\Dbal\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\StringType;

abstract class AbstractStringableType extends StringType
{
    public const string NAME = 'abstract_stringable';

    public const string PHP_TYPE_FQCN = '';

    public function convertToPHPValue($value, AbstractPlatform $platform): ?\Stringable
    {
        $phpTypeFqcn = $this->phpTypeFqcn();

        if ($value instanceof $phpTypeFqcn) {
            return $value;
        }

        if (null === $value || '' === $value) {
            return null;
        }

        if (!is_callable([$phpTypeFqcn, 'fromString'])) {
            throw new \RuntimeException("Method $phpTypeFqcn::fromString() is not callable. Check if it exists.");
        }

        try {
            return $phpTypeFqcn::fromString($value);
        } catch (\Throwable $e) {
            throw ConversionException::conversionFailed($value, static::NAME, $e);
        }
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (null === $value || '' === $value) {
            return null;
        }

        $phpTypeFqcn = $this->phpTypeFqcn();

        if ($value instanceof $phpTypeFqcn) {
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

    protected function phpTypeFqcn(): string
    {
        return static::PHP_TYPE_FQCN;
    }
}
