<?php

declare(strict_types=1);

namespace ReleaseManagement\ReleasePublication\Domain\Model\Status;

use ReleaseManagement\ReleasePublication\Domain\Exception\ReleasePublicationStatusTransitionException;
use ReleaseManagement\ReleasePublication\Domain\Model\ReleasePublicationInterface;
use ReleaseManagement\Shared\Domain\Model\ContinuousIntegration\ContinuousIntegrationClientInterface;
use ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\SourceCodeRepositoryInterface;

interface StatusInterface extends \Stringable
{
    /**
     * @throws ReleasePublicationStatusTransitionException
     */
    public function createFrontendBranch(SourceCodeRepositoryInterface $repository, ReleasePublicationInterface $context): void;

    /**
     * @throws ReleasePublicationStatusTransitionException
     */
    public function createBackendBranch(SourceCodeRepositoryInterface $repository, ReleasePublicationInterface $context): void;

    /**
     * @throws ReleasePublicationStatusTransitionException
     */
    public function awaitLatestFrontendPipeline(
        ContinuousIntegrationClientInterface $ciClient,
        ReleasePublicationInterface $context,
        \DateInterval $maxAwaitingTime = null,
    ): void;
}
