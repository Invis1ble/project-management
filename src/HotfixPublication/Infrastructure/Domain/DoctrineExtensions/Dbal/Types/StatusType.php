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
            if (is_string($value)) {
                $name = $value;
                $context = null;
            } else {
                $name = $value['name'];
                $context = $value['context'] ?? null;
            }

            return StatusFactory::createStatus(
                name: Dictionary::from($name),
                context: $context,
            );
        } catch (\Throwable $e) {
            throw ConversionException::conversionFailed($value, self::NAME, $e);
        }
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): array|string
    {
        if ($value instanceof StatusInterface) {
            $context = $value->context()
                ->toArray();

            if (null === $context) {
                return (string) $value;
            }

            return json_encode([
                'name' => (string) $value,
                'context' => $context,
            ]);
        }

        if (is_string($value)) {
            if (null !== Dictionary::tryFrom($value)) {
                return (string) $value;
            }

            $value = json_decode($value, true);
        }

        if (is_array($value) && isset($value['name'])) {
            return $value;
        }

        throw ConversionException::conversionFailed($value, self::NAME);
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
