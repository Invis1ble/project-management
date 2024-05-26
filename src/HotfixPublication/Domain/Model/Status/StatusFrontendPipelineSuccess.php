<?php

declare(strict_types=1);

namespace ProjectManagement\HotfixPublication\Domain\Model\Status;

use ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationInterface;
use ProjectManagement\HotfixPublication\Domain\Model\TaskTracker\TaskTrackerInterface;
use ProjectManagement\Shared\Domain\Model\ContinuousIntegration\ContinuousIntegrationClientInterface;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequestManagerInterface;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch\Name;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\SetFrontendApplicationBranchNameCommitFactoryInterface;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\SourceCodeRepositoryInterface;

final readonly class StatusFrontendPipelineSuccess extends StatusFrontendPipelineFinished
{
    public function proceedToNext(
        MergeRequestManagerInterface $mergeRequestManager,
        SourceCodeRepositoryInterface $frontendSourceCodeRepository,
        SourceCodeRepositoryInterface $backendSourceCodeRepository,
        ContinuousIntegrationClientInterface $frontendCiClient,
        ContinuousIntegrationClientInterface $backendCiClient,
        SetFrontendApplicationBranchNameCommitFactoryInterface $setFrontendApplicationBranchNameCommitFactory,
        TaskTrackerInterface $taskTracker,
        HotfixPublicationInterface $context,
    ): void {
        $commit = $setFrontendApplicationBranchNameCommitFactory->createSetFrontendApplicationBranchNameCommit(
            targetBranchName: $context->branchName(),
            startBranchName: Name::fromString('develop'),
        );

        $backendSourceCodeRepository->commit(
            branchName: $commit->branchName,
            message: $commit->message,
            actions: $commit->actions,
            startBranchName: $commit->startBranchName,
        );

        $this->setReleaseStatus($context, new StatusBackendBranchCreated());
    }

    public function __toString(): string
    {
        return Dictionary::FrontendPipelineSuccess->value;
    }
}
