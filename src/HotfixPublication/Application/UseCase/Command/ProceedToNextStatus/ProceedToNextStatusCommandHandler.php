<?php

declare(strict_types=1);

namespace ProjectManagement\HotfixPublication\Application\UseCase\Command\ProceedToNextStatus;

use ProjectManagement\HotfixPublication\Application\UseCase\Command\HotfixPublicationRepositoryAwareCommandHandler;
use ProjectManagement\HotfixPublication\Domain\Model\TaskTracker\TaskTrackerInterface;
use ProjectManagement\HotfixPublication\Domain\Repository\HotfixPublicationRepositoryInterface;
use ProjectManagement\Shared\Domain\Model\ContinuousIntegration\ContinuousIntegrationClientInterface;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequestManagerInterface;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\NewCommit\SetFrontendApplicationBranchNameCommitFactoryInterface;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\SourceCodeRepositoryInterface;

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
        );

        $this->storeHotfixPublication($hotfixPublication);
    }
}
