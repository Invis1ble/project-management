<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Application\UseCase\Command\ProceedToNextStatus;

use Invis1ble\ProjectManagement\ReleasePublication\Application\UseCase\Command\ReleasePublicationRepositoryAwareCommandHandler;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\TaskTracker\TaskTrackerInterface;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Repository\ReleasePublicationRepositoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\ContinuousIntegrationClientInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestManagerInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\UpdateExtraDeploymentBranchMergeRequestFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\SetFrontendApplicationBranchNameCommitFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\SourceCodeRepositoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\StatusProviderInterface;

final readonly class ProceedToNextStatusCommandHandler extends ReleasePublicationRepositoryAwareCommandHandler
{
    public function __construct(
        ReleasePublicationRepositoryInterface $repository,
        private MergeRequestManagerInterface $mergeRequestManager,
        private SourceCodeRepositoryInterface $frontendSourceCodeRepository,
        private SourceCodeRepositoryInterface $backendSourceCodeRepository,
        private ContinuousIntegrationClientInterface $frontendCiClient,
        private ContinuousIntegrationClientInterface $backendCiClient,
        private SetFrontendApplicationBranchNameCommitFactoryInterface $setFrontendApplicationBranchNameCommitFactory,
        private UpdateExtraDeploymentBranchMergeRequestFactoryInterface $updateExtraDeploymentBranchMergeRequestFactory,
        private TaskTrackerInterface $taskTracker,
        private StatusProviderInterface $issueStatusProvider,
        private \DateInterval $pipelineMaxAwaitingTime,
        private \DateInterval $pipelineTickInterval,
    ) {
        parent::__construct($repository);
    }

    public function __invoke(ProceedToNextStatusCommand $command): void
    {
        $releasePublication = $this->getReleasePublication($command->id);

        $releasePublication->proceedToNextStatus(
            mergeRequestManager: $this->mergeRequestManager,
            frontendSourceCodeRepository: $this->frontendSourceCodeRepository,
            backendSourceCodeRepository: $this->backendSourceCodeRepository,
            frontendCiClient: $this->frontendCiClient,
            backendCiClient: $this->backendCiClient,
            setFrontendApplicationBranchNameCommitFactory: $this->setFrontendApplicationBranchNameCommitFactory,
            updateExtraDeploymentBranchMergeRequestFactory: $this->updateExtraDeploymentBranchMergeRequestFactory,
            taskTracker: $this->taskTracker,
            issueStatusProvider: $this->issueStatusProvider,
            pipelineMaxAwaitingTime: $this->pipelineMaxAwaitingTime,
            pipelineTickInterval: $this->pipelineTickInterval,
        );

        $this->storeReleasePublication($releasePublication);
    }
}
