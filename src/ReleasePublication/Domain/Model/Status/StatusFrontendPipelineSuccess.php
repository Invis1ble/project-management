<?php

declare(strict_types=1);

namespace ProjectManagement\ReleasePublication\Domain\Model\Status;

use ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationInterface;
use ProjectManagement\ReleasePublication\Domain\Model\TaskTracker\TaskTrackerInterface;
use ProjectManagement\Shared\Domain\Model\ContinuousIntegration\ContinuousIntegrationClientInterface;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestManagerInterface;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch\Name;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\SetFrontendApplicationBranchNameCommitFactoryInterface;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\SourceCodeRepositoryInterface;

final readonly class StatusFrontendPipelineSuccess extends StatusFrontendPipelineNotInProgress
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
        $newCommit = $setFrontendApplicationBranchNameCommitFactory->createSetFrontendApplicationBranchNameCommit(
            targetBranchName: $context->branchName(),
            startBranchName: Name::fromString('develop'),
        );

        $newCommit?->commit($backendSourceCodeRepository);

        $this->setPublicationStatus($context, new StatusBackendBranchCreated());
    }

    public function __toString(): string
    {
        return Dictionary::FrontendPipelineSuccess->value;
    }
}
