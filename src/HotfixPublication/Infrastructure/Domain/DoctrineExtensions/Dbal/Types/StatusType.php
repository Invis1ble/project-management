<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Infrastructure\Domain\DoctrineExtensions\Dbal\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\StringType;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status\Dictionary;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status\StatusFactory;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\Status\StatusInterface;

final class StatusType extends StringType
{
    public const string NAME = 'hotfix_publication_status';

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
