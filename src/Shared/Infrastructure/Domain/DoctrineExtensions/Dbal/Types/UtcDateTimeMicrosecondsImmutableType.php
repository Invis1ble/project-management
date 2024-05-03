<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Infrastructure\Domain\DoctrineExtensions\Dbal\Types;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\DateTimeImmutableType;

class UtcDateTimeMicrosecondsImmutableType extends DateTimeImmutableType
{
    public const string NAME = 'utc_datetime_microseconds_immutable';

    public const string PLATFORM_DATE_TIME_FORMAT = 'Y-m-d H:i:s.u';

    protected static ?\DateTimeZone $utcTimezone;

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return match (true) {
            $platform instanceof AbstractMySQLPlatform => isset($column['version']) && $column['version'] === true
                ? 'TIMESTAMP(6)'
                : 'DATETIME(6)',
            $platform instanceof PostgreSQLPlatform => 'TIMESTAMP(6) WITHOUT TIME ZONE',
        };
    }

    /**
     * {@inheritdoc}
     *
     * @throws ConversionException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        if ($value instanceof \DateTimeImmutable) {
            $value->setTimezone(static::getUTC());

            return $value->format($this->platformDateTimeFormatString($platform));
        }

        throw ConversionException::conversionFailedInvalidType(
            $value,
            $this->getName(),
            ['null', \DateTimeImmutable::class],
        );
    }

    /**
     * {@inheritdoc}
     *
     * @throws ConversionException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?\DateTimeImmutable
    {
        if (null === $value || $value instanceof \DateTimeImmutable) {
            return $value;
        }

        $utcTz = static::getUTC();

        $dateTime = \DateTimeImmutable::createFromFormat(
            $this->platformDateTimeFormatString($platform),
            $value,
            $utcTz,
        );

        if (false !== $dateTime) {
            return $dateTime;
        }

        try {
            return new \DateTimeImmutable($value);
        } catch (\Exception $e) {
            throw ConversionException::conversionFailedFormat(
                $value,
                $this->getName(),
                $this->platformDateTimeFormatString($platform),
                $e,
            );
        }
    }

    public function getName(): string
    {
        return static::NAME;
    }

    /**
     * @return \DateTimeZone
     */
    protected static function getUTC(): \DateTimeZone
    {
        if (!isset(static::$utcTimezone)) {
            static::$utcTimezone = new \DateTimeZone('UTC');
        }

        return static::$utcTimezone;
    }

    protected function platformDateTimeFormatString(AbstractPlatform $platform): string
    {
        return static::PLATFORM_DATE_TIME_FORMAT;
    }
}
