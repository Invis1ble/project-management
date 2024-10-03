<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\Status;

use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationInterface;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\TaskTracker\TaskTrackerInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\ContinuousIntegrationClientInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch\Name;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\SetFrontendApplicationBranchNameCommitFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\SourceCodeRepositoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\StatusProviderInterface;

final readonly class StatusBackendMergeRequestIntoDevelopmentBranchMerged extends AbstractStatus
{
    public function proceedToNext(
        MergeRequest\MergeRequestManagerInterface $mergeRequestManager,
        SourceCodeRepositoryInterface $frontendSourceCodeRepository,
        SourceCodeRepositoryInterface $backendSourceCodeRepository,
        ContinuousIntegrationClientInterface $frontendCiClient,
        ContinuousIntegrationClientInterface $backendCiClient,
        SetFrontendApplicationBranchNameCommitFactoryInterface $setFrontendApplicationBranchNameCommitFactory,
        MergeRequest\UpdateExtraDeploymentBranchMergeRequestFactoryInterface $updateExtraDeploymentBranchMergeRequestFactory,
        TaskTrackerInterface $taskTracker,
        StatusProviderInterface $issueStatusProvider,
        \DateInterval $pipelineTickInterval,
        ReleasePublicationInterface $publication,
        \DateInterval $pipelineMaxAwaitingTime,
    ): void {
        $newCommit = $setFrontendApplicationBranchNameCommitFactory->createSetFrontendApplicationBranchNameCommit(
            branchName: Name::fromString('develop'),
        );

        $newCommit?->commit($backendSourceCodeRepository);

        $this->setPublicationStatus($publication, new StatusFrontendApplicationBranchSetToDevelopment());
    }

    public function __toString(): string
    {
        return Dictionary::BackendMergeRequestIntoDevelopmentBranchMerged->value;
    }
}
