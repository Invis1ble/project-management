<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Application\UseCase\Command\ProceedToNextStatus;

use Invis1ble\ProjectManagement\HotfixPublication\Application\UseCase\Command\HotfixPublicationRepositoryAwareCommandHandler;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\TaskTracker\TaskTrackerInterface;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Repository\HotfixPublicationRepositoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\ContinuousIntegrationClientInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\ContinuousIntegration\Project\ProjectResolverInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestManagerInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\SetFrontendApplicationBranchNameCommitFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\SourceCodeRepositoryInterface;

final readonly class ProceedToNextStatusCommandHandler extends HotfixPublicationRepositoryAwareCommandHandler
{
    public function __construct(
        HotfixPublicationRepositoryInterface $repository,
        private MergeRequestManagerInterface $mergeRequestManager,
        private SourceCodeRepositoryInterface $frontendSourceCodeRepository,
        private SourceCodeRepositoryInterface $backendSourceCodeRepository,
        private ContinuousIntegrationClientInterface $frontendCiClient,
        private ContinuousIntegrationClientInterface $backendCiClient,
        private SetFrontendApplicationBranchNameCommitFactoryInterface $setFrontendApplicationBranchNameCommitFactory,
        private TaskTrackerInterface $taskTracker,
        private ProjectResolverInterface $projectResolver,
        private \DateInterval $pipelineMaxAwaitingTime,
        private \DateInterval $pipelineTickInterval,
    ) {
        parent::__construct($repository);
    }

    public function __invoke(ProceedToNextStatusCommand $command): void
    {
        $hotfixPublication = $this->getHotfixPublication($command->id);

        $hotfixPublication->proceedToNextStatus(
            mergeRequestManager: $this->mergeRequestManager,
            frontendSourceCodeRepository: $this->frontendSourceCodeRepository,
            backendSourceCodeRepository: $this->backendSourceCodeRepository,
            frontendCiClient: $this->frontendCiClient,
            backendCiClient: $this->backendCiClient,
            setFrontendApplicationBranchNameCommitFactory: $this->setFrontendApplicationBranchNameCommitFactory,
            taskTracker: $this->taskTracker,
            projectResolver: $this->projectResolver,
            pipelineMaxAwaitingTime: $this->pipelineMaxAwaitingTime,
            pipelineTickInterval: $this->pipelineTickInterval,
        );

        $this->storeHotfixPublication($hotfixPublication);
    }
}
