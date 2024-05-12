<?php

declare(strict_types=1);

namespace ReleaseManagement\ReleasePublication\Application\Saga\PrepareRelease;

use Broadway\Saga\State;
use Broadway\Saga\State\Criteria;
use Invis1ble\Messenger\Command\CommandBusInterface;
use ReleaseManagement\ReleasePublication\Application\UseCase\Command\AwaitFrontendPipeline\AwaitFrontendPipelineCommand;
use ReleaseManagement\ReleasePublication\Application\UseCase\Command\CreateBackendBranch\CreateBackendBranchCommand;
use ReleaseManagement\ReleasePublication\Application\UseCase\Command\CreateFrontendBranch\CreateFrontendBranchCommand;
use ReleaseManagement\ReleasePublication\Domain\Event\ReleasePublicationCreated;
use ReleaseManagement\ReleasePublication\Domain\Model\ProjectResolverInterface;
use ReleaseManagement\ReleasePublication\Domain\Model\ReleasePublicationId;
use ReleaseManagement\ReleasePublication\Domain\Model\SourceCodeRepository\Branch\Name;
use ReleaseManagement\Shared\Application\Saga\StaticallyConfiguredCommandBusAwareSaga;
use ReleaseManagement\Shared\Domain\Event\BranchCreated;
use ReleaseManagement\Shared\Domain\Event\LatestPipelineStatusChanged;
use ReleaseManagement\Shared\Domain\Model\ContinuousIntegration\Pipeline\Status;

final class PrepareReleaseSaga extends StaticallyConfiguredCommandBusAwareSaga
{
    public function __construct(
        CommandBusInterface $commandBus,
        private readonly ProjectResolverInterface $projectResolver,
    ) {
        parent::__construct($commandBus);
    }

    public static function configuration(): array
    {
        return [
            ReleasePublicationCreated::class => fn () => null,
            BranchCreated::class => fn (BranchCreated $event) => $this->createCriteria($event->branchName),
            LatestPipelineStatusChanged::class => fn (LatestPipelineStatusChanged $event) => $this->createCriteria($event->branchName),
            DefaultFrontendBranchSet::class => fn (DefaultFrontendBranchSet $event) => new Criteria(['releaseId' => $event->releaseId]),
            ReleaseCandidateRenamed::class => fn (ReleaseCandidateRenamed $event) => new Criteria(['releaseId' => $event->releaseId]),
            ReleaseCandidateCreated::class => fn (ReleaseCandidateCreated $event) => new Criteria(['releaseId' => $event->releaseId]),
        ];
    }

    protected function handleReleaseCreated(ReleasePublicationCreated $event, State $state): void
    {
        $state->set('releaseId', $event->id);

        $this->dispatchCommand(new CreateFrontendBranchCommand($state->get('releaseId')));
    }

    protected function handleBranchCreated(BranchCreated $event, State $state): void
    {
        if ($this->projectResolver->frontend($event->projectId)) {
            $this->dispatchCommand(new AwaitFrontendPipelineCommand($state->get('releaseId')));
        } elseif ($this->projectResolver->backend($event->projectId)) {
            $this->dispatchCommand(new SetDefaultFrontendBranchCommand($state->get('releaseId')));
        }
    }

    protected function handleLatestPipelineStatusChanged(LatestPipelineStatusChanged $event, State $state): void
    {
        if (!$this->projectResolver->frontend($event->projectId) || $event->status->inProgress()) {
            return;
        }

        try {
            $command = match ($event->status) {
                Status::Success => new CreateBackendBranchCommand($state->get('releaseId')),
                Status::Failed => new RetryFrontendPipelineCommand($state->get('releaseId'), $event->pipelineId),
            };
        } catch (\UnhandledMatchError $e) {
            $state->setDone();

            throw $e;
        }

        $this->dispatchCommand($command);
    }

    protected function handleDefaultFrontendBranchSet(DefaultFrontendBranchSet $event, State $state): void
    {
        $this->dispatchCommand(new RenameReleaseCandidate($state->get('releaseId')));
    }

    protected function handleReleaseCandidateRenamed(ReleaseCandidateRenamed $event, State $state): void
    {
        $this->dispatchCommand(new CreateReleaseCandidate($state->get('releaseId')));
    }

    protected function handleReleaseCandidateCreated(ReleaseCandidateCreated $event, State $state): void
    {
        $state->setDone();
    }

    private function createCriteria(Name $branchName): Criteria
    {
        return new Criteria([
            'releaseId' => $branchName instanceof Name ? ReleasePublicationId::generate($branchName) : null,
        ]);
    }
}
