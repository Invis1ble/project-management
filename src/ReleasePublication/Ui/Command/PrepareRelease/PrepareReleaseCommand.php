<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Ui\Command\PrepareRelease;

use Invis1ble\Messenger\Command\CommandBusInterface;
use Invis1ble\Messenger\Query\QueryBusInterface;
use Invis1ble\ProjectManagement\ReleasePublication\Application\UseCase\Command\CreateReleasePublication\CreateReleasePublicationCommand;
use Invis1ble\ProjectManagement\ReleasePublication\Application\UseCase\Command\ProceedToNextStatus\ProceedToNextStatusCommand;
use Invis1ble\ProjectManagement\ReleasePublication\Application\UseCase\Query\GetLatestRelease\GetLatestReleaseQuery;
use Invis1ble\ProjectManagement\ReleasePublication\Application\UseCase\Query\GetLatestReleasePublication\GetLatestReleasePublicationQuery;
use Invis1ble\ProjectManagement\ReleasePublication\Application\UseCase\Query\GetReleasePublication\GetReleasePublicationQuery;
use Invis1ble\ProjectManagement\ReleasePublication\Application\UseCase\Query\GetTasksInActiveSprint\GetTasksInActiveSprintQuery;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationId;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationInterface;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\SourceCodeRepository\Branch;
use Invis1ble\ProjectManagement\ReleasePublication\Ui\Command\ReleasePublicationAwareCommand;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch\Name as BasicBranchName;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\GuiUrlFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Version;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[AsCommand(name: 'pm:release:prepare', description: 'Prepares a new release')]
final class PrepareReleaseCommand extends ReleasePublicationAwareCommand
{
    private readonly Issue\Status $statusReadyToMerge;

    private readonly Issue\Status $statusReleaseCandidate;

    public function __construct(
        QueryBusInterface $queryBus,
        CommandBusInterface $commandBus,
        GuiUrlFactoryInterface $issueGuiUrlFactory,
        SerializerInterface $serializer,
        \DateInterval $pipelineMaxAwaitingTime,
        Issue\StatusProviderInterface $statusProvider,
    ) {
        $this->statusReadyToMerge = $statusProvider->readyToMerge();
        $this->statusReleaseCandidate = $statusProvider->releaseCandidate();

        parent::__construct(
            queryBus: $queryBus,
            commandBus: $commandBus,
            serializer: $serializer,
            issueStatusProvider: $statusProvider,
            issueGuiUrlFactory: $issueGuiUrlFactory,
            pipelineMaxAwaitingTime: $pipelineMaxAwaitingTime,
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);

        $this->io->title('Preparing a new release');

        $resume = $input->getOption('resume');

        if (false === $resume) {
            $latestReleaseBranchName = $this->latestReleaseBranchName();

            $tasks = $this->tasksToRelease();
            $tasks = $this->enrichIssuesWithMergeRequests(
                issues: $tasks,
                targetBranchName: BasicBranchName::fromString('develop'),
            );

            $this->caption("Latest release branch name: $latestReleaseBranchName");

            $newReleaseBranchName = $this->io->ask(
                question: 'New release branch name',
                default: (string) $latestReleaseBranchName->bumpMinorVersion(),
                validator: function (string $branchName) use ($latestReleaseBranchName): Branch\Name {
                    $branchName = Branch\Name::fromString($branchName);

                    if (!$branchName->versionNewerThan($latestReleaseBranchName)) {
                        throw new \InvalidArgumentException("Provided version must be greater than latest release $latestReleaseBranchName version");
                    }

                    return $branchName;
                },
            );

            $publicationId = ReleasePublicationId::fromBranchName($newReleaseBranchName);

            if ($tasks->empty()) {
                $this->caption('No tasks to release in the active sprint');

                return Command::SUCCESS;
            }
        } else {
            if (is_string($resume)) {
                $publicationId = ReleasePublicationId::fromString($resume);
                $publication = $this->getPublication(new GetReleasePublicationQuery($publicationId));
            } else {
                $publication = $this->getPublication(new GetLatestReleasePublicationQuery());
                $publicationId = $publication->id();
            }

            $newReleaseBranchName = $publication->branchName();
            $tasks = $publication->tasks();
        }

        $this->io->section('Summary');

        $this->caption('New release branch name');
        $this->io->text("<fg=bright-magenta;bg=black;options=bold> $newReleaseBranchName </>");

        $this->listTasksToRelease($tasks);

        $mergeRequestsToMerge = $tasks->mergeRequestsToMerge();

        if ($mergeRequestsToMerge->empty()) {
            $this->caption('No Merge requests will be merged');
        } else {
            $this->caption('Merge requests will be merged');
            $this->listMergeRequests($tasks->mergeRequestsToMerge());
        }

        if ($input->getOption('dry-run')) {
            return Command::SUCCESS;
        }

        $confirmed = $this->io->confirm('OK', false);

        if (!$confirmed) {
            $this->abort();
        }

        if (false === $resume) {
            $this->commandBus->dispatch(new CreateReleasePublicationCommand(
                branchName: $newReleaseBranchName,
                tasks: $tasks,
            ));
        } else {
            $this->commandBus->dispatch(new ProceedToNextStatusCommand($publicationId));
        }

        return $this->showProgressLog(
            query: new GetReleasePublicationQuery($publicationId),
            inFinalState: fn (ReleasePublicationInterface $publication): bool => $publication->prepared(),
        );
    }

    private function latestReleaseBranchName(): Branch\Name
    {
        $this->phase('Fetching latest release...');

        /** @var Version\Version $release */
        $release = $this->queryBus->ask(new GetLatestReleaseQuery());

        if (null === $release) {
            throw new \UnexpectedValueException('No release found');
        }

        if (!$release->released) {
            throw new \UnexpectedValueException("Latest release $release->name not released yet");
        }

        return Branch\Name::fromString((string) $release->name);
    }

    private function tasksToRelease(): Issue\IssueList
    {
        $this->phase('Fetching tasks to release...');

        /** @var Issue\IssueList $tasks */
        $tasks = $this->queryBus->ask(new GetTasksInActiveSprintQuery(
            $this->statusReadyToMerge,
            $this->statusReleaseCandidate,
        ));

        if ($tasks->empty()) {
            $this->caption('No tasks to release found in the active sprint');

            return $tasks;
        }

        $this->listTasksToRelease($tasks);

        $tasksToMerge = $tasks->filter(
            fn (Issue\Issue $task): bool => $task->status->equals($this->statusReadyToMerge)
                && $this->io->confirm("Merge $task | $task->summary"),
        );

        if ($tasksToMerge->empty()) {
            $this->caption('No tasks to merge');

            return $tasks;
        }

        $this->caption('Tasks to merge');
        $this->listIssues($tasksToMerge);

        return $tasksToMerge;
    }

    private function listTasksToRelease(Issue\IssueList $tasks): void
    {
        $releaseCandidateTasks = $tasks->onlyInStatus($this->statusReleaseCandidate);
        $readyToMergeTasks = $tasks->onlyInStatus($this->statusReadyToMerge);

        if (!$readyToMergeTasks->empty()) {
            $this->caption("$this->statusReleaseCandidate tasks");
            $this->listIssues($releaseCandidateTasks);
        }

        if (!$readyToMergeTasks->empty()) {
            $this->caption("$this->statusReadyToMerge tasks");
            $this->listIssues($readyToMergeTasks);
        }
    }
}
