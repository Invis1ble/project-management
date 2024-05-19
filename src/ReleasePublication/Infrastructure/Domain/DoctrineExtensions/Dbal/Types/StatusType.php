<?php

declare(strict_types=1);

namespace ReleaseManagement\ReleasePublication\Infrastructure\Domain\DoctrineExtensions\Dbal\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\StringType;
use ReleaseManagement\ReleasePublication\Domain\Model\Status\Dictionary;
use ReleaseManagement\ReleasePublication\Domain\Model\Status\StatusFactory;
use ReleaseManagement\ReleasePublication\Domain\Model\Status\StatusInterface;

final class StatusType extends StringType
{
    public const string NAME = 'release_publication_status';

    /**
     * {@inheritdoc}
     *
     * @throws ConversionException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): StatusInterface
    {
        if ($value instanceof StatusInterface) {
            return $value;
        }

        try {
            return StatusFactory::createStatus(Dictionary::from($value));
        } catch (\Throwable $e) {
            throw ConversionException::conversionFailed($value, self::NAME, $e);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws ConversionException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): string
    {
        if ($value instanceof StatusInterface || (is_string($value) && null !== Dictionary::tryFrom($value))) {
            return (string) $value;
        }

        throw ConversionException::conversionFailed($value, self::NAME);
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
