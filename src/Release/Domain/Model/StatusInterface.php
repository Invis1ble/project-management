<?php

declare(strict_types=1);

namespace ReleaseManagement\Release\Domain\Model;

use ReleaseManagement\Release\Domain\Exception\ReleaseStatusTransitionException;
use ReleaseManagement\Shared\Domain\Model\ContinuousIntegrationClientInterface;
use ReleaseManagement\Shared\Domain\Model\SourceCodeRepositoryInterface;

interface StatusInterface extends \Stringable
{
    /**
     * @throws ReleaseStatusTransitionException
     */
    public function createFrontendBranch(SourceCodeRepositoryInterface $repository, ReleaseInterface $context): void;

    /**
     * @throws ReleaseStatusTransitionException
     */
    public function createBackendBranch(SourceCodeRepositoryInterface $repository, ReleaseInterface $context): void;

    /**
     * @throws ReleaseStatusTransitionException
     */
    public function awaitLatestFrontendPipeline(
        ContinuousIntegrationClientInterface $ciClient,
        ReleaseInterface $context,
        \DateInterval $maxAwaitingTime = null,
    ): void;
}
