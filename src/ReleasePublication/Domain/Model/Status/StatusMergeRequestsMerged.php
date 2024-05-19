<?php

declare(strict_types=1);

namespace ReleaseManagement\ReleasePublication\Domain\Model\Status;

use ReleaseManagement\ReleasePublication\Domain\Model\ReleasePublicationInterface;
use ReleaseManagement\ReleasePublication\Domain\Model\TaskTracker\TaskTrackerInterface;
use ReleaseManagement\Shared\Domain\Model\ContinuousIntegration\ContinuousIntegrationClientInterface;
use ReleaseManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequestManagerInterface;
use ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\Branch\Name;
use ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\SetFrontendApplicationBranchNameCommitFactoryInterface;
use ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\SourceCodeRepositoryInterface;

final readonly class StatusMergeRequestsMerged extends AbstractStatus
{
    public function proceedToNext(
        MergeRequestManagerInterface $mergeRequestManager,
        SourceCodeRepositoryInterface $frontendSourceCodeRepository,
        SourceCodeRepositoryInterface $backendSourceCodeRepository,
        ContinuousIntegrationClientInterface $frontendCiClient,
        ContinuousIntegrationClientInterface $backendCiClient,
        SetFrontendApplicationBranchNameCommitFactoryInterface $setFrontendApplicationBranchNameCommitFactory,
        TaskTrackerInterface $taskTracker,
        ReleasePublicationInterface $context,
    ): void {
        $frontendSourceCodeRepository->createBranch($context->branchName(), Name::fromString('develop'));

        $this->setReleaseStatus($context, new StatusFrontendBranchCreated());
    }

    public function __toString(): string
    {
        return Dictionary::MergeRequestsMerged->value;
    }
}
