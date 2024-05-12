<?php

declare(strict_types=1);

namespace ReleaseManagement\ReleasePublication\Infrastructure\Domain\DoctrineExtensions\Dbal\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\StringType;
use ReleaseManagement\ReleasePublication\Domain\Model\Status\Dictionary;
use ReleaseManagement\ReleasePublication\Domain\Model\Status\StatusCreated;
use ReleaseManagement\ReleasePublication\Domain\Model\Status\StatusFrontendBranchCreated;
use ReleaseManagement\ReleasePublication\Domain\Model\Status\StatusFrontendPipelineCanceled;
use ReleaseManagement\ReleasePublication\Domain\Model\Status\StatusFrontendPipelineCreated;
use ReleaseManagement\ReleasePublication\Domain\Model\Status\StatusFrontendPipelineFailed;
use ReleaseManagement\ReleasePublication\Domain\Model\Status\StatusFrontendPipelineManual;
use ReleaseManagement\ReleasePublication\Domain\Model\Status\StatusFrontendPipelinePending;
use ReleaseManagement\ReleasePublication\Domain\Model\Status\StatusFrontendPipelinePreparing;
use ReleaseManagement\ReleasePublication\Domain\Model\Status\StatusFrontendPipelineRunning;
use ReleaseManagement\ReleasePublication\Domain\Model\Status\StatusFrontendPipelineScheduled;
use ReleaseManagement\ReleasePublication\Domain\Model\Status\StatusFrontendPipelineSkipped;
use ReleaseManagement\ReleasePublication\Domain\Model\Status\StatusFrontendPipelineStuck;
use ReleaseManagement\ReleasePublication\Domain\Model\Status\StatusFrontendPipelineSuccess;
use ReleaseManagement\ReleasePublication\Domain\Model\Status\StatusFrontendPipelineWaitingForResource;
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
