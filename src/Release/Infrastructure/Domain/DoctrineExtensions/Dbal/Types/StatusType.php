<?php

declare(strict_types=1);

namespace ReleaseManagement\Release\Infrastructure\Domain\DoctrineExtensions\Dbal\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\StringType;
use ReleaseManagement\Release\Domain\Model\Status\Dictionary;
use ReleaseManagement\Release\Domain\Model\Status\StatusCreated;
use ReleaseManagement\Release\Domain\Model\Status\StatusFrontendBranchCreated;
use ReleaseManagement\Release\Domain\Model\Status\StatusFrontendPipelineCanceled;
use ReleaseManagement\Release\Domain\Model\Status\StatusFrontendPipelineCreated;
use ReleaseManagement\Release\Domain\Model\Status\StatusFrontendPipelineFailed;
use ReleaseManagement\Release\Domain\Model\Status\StatusFrontendPipelineManual;
use ReleaseManagement\Release\Domain\Model\Status\StatusFrontendPipelinePending;
use ReleaseManagement\Release\Domain\Model\Status\StatusFrontendPipelinePreparing;
use ReleaseManagement\Release\Domain\Model\Status\StatusFrontendPipelineRunning;
use ReleaseManagement\Release\Domain\Model\Status\StatusFrontendPipelineScheduled;
use ReleaseManagement\Release\Domain\Model\Status\StatusFrontendPipelineSkipped;
use ReleaseManagement\Release\Domain\Model\Status\StatusFrontendPipelineStuck;
use ReleaseManagement\Release\Domain\Model\Status\StatusFrontendPipelineSuccess;
use ReleaseManagement\Release\Domain\Model\Status\StatusFrontendPipelineWaitingForResource;
use ReleaseManagement\Release\Domain\Model\StatusInterface;

final class StatusType extends StringType
{
    public const string NAME = 'release_status';

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
            return match ($value) {
                Dictionary::Created->value => new StatusCreated(),
                Dictionary::FrontendBranchCreated->value => new StatusFrontendBranchCreated(),
                Dictionary::FrontendPipelineCreated->value => new StatusFrontendPipelineCreated(),
                Dictionary::FrontendPipelineWaitingForResource->value => new StatusFrontendPipelineWaitingForResource(),
                Dictionary::FrontendPipelinePreparing->value => new StatusFrontendPipelinePreparing(),
                Dictionary::FrontendPipelinePending->value => new StatusFrontendPipelinePending(),
                Dictionary::FrontendPipelineRunning->value => new StatusFrontendPipelineRunning(),
                Dictionary::FrontendPipelineSuccess->value => new StatusFrontendPipelineSuccess(),
                Dictionary::FrontendPipelineFailed->value => new StatusFrontendPipelineFailed(),
                Dictionary::FrontendPipelineCanceled->value => new StatusFrontendPipelineCanceled(),
                Dictionary::FrontendPipelineSkipped->value => new StatusFrontendPipelineSkipped(),
                Dictionary::FrontendPipelineManual->value => new StatusFrontendPipelineManual(),
                Dictionary::FrontendPipelineScheduled->value => new StatusFrontendPipelineScheduled(),
                Dictionary::FrontendPipelineStuck->value => new StatusFrontendPipelineStuck(),
            };
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
