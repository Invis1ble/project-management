<?php

declare(strict_types=1);

namespace ReleaseManagement\ReleasePublication\Ui\Command\PrepareRelease;

use Invis1ble\Messenger\Command\CommandBusInterface;
use Invis1ble\Messenger\Query\QueryBusInterface;
use ReleaseManagement\ReleasePublication\Application\UseCase\Query\GetLatestRelease\GetLatestReleaseQuery;
use ReleaseManagement\ReleasePublication\Application\UseCase\Query\GetReadyToMergeTasksInActiveSprint\GetReadyToMergeTasksInActiveSprintQuery;
use ReleaseManagement\ReleasePublication\Domain\Model\SourceCodeRepository\Branch\Name;
use ReleaseManagement\Shared\Application\UseCase\Query\GetIssueMergeRequests\GetIssueMergeRequestsQuery;
use ReleaseManagement\Shared\Application\UseCase\Query\GetMergeRequestDetails\GetMergeRequestDetailsQuery;
use ReleaseManagement\Shared\Application\UseCase\Query\GetProjectSupported\GetProjectSupportedQuery;
use ReleaseManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Details;
use ReleaseManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequest;
use ReleaseManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestList;
use ReleaseManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Status;
use ReleaseManagement\Shared\Domain\Model\SourceCodeRepository\Branch\Name as BasicBranchName;
use ReleaseManagement\Shared\Domain\Model\TaskTracker\Issue\GuiUrlFactoryInterface;
use ReleaseManagement\Shared\Domain\Model\TaskTracker\Issue\Issue;
use ReleaseManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;
use ReleaseManagement\Shared\Domain\Model\TaskTracker\Version\Version;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'release:prepare', description: 'Prepares new release')]
final class PrepareReleaseCommand extends Command
{
    private readonly SymfonyStyle $io;

    private const array NO_MERGE_REQUESTS_ACTION_IDS = [
        'ABORT' => 0,
        'RELOAD' => 1,
        'CONTINUE' => 2,
    ];

    private const array NO_MERGE_REQUESTS_ACTIONS = [
        'Abort release preparation' => self::NO_MERGE_REQUESTS_ACTION_IDS['ABORT'],
        'Load merge requests for the task again' => self::NO_MERGE_REQUESTS_ACTION_IDS['RELOAD'],
        'Continue without merge requests' => self::NO_MERGE_REQUESTS_ACTION_IDS['CONTINUE'],
    ];

    private array $userChoices = [
        'NO_MERGE_REQUESTS_ACTION' => null,
    ];

    public function __construct(
        private readonly QueryBusInterface $queryBus,
        private readonly CommandBusInterface $commandBus,
        private readonly GuiUrlFactoryInterface $issueGuiUrlFactory,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $this->io->title('Preparing new release');





        $newReleaseBranchName = $this->newReleaseBranchName();
        $tasks = $this->readyToMergeTasks();

        $tasks = new IssueList(
            ...$tasks->map(fn (Issue $task): Issue => $this->taskWithMergeRequests($task)),
        );

        $this->io->section('Summary');

        $this->io->block('New release branch name', null, 'fg=green');
        $this->io->block((string) $newReleaseBranchName);

        $this->io->block('Tasks in Ready to Merge from the active sprint', null, 'fg=green');
        $this->listIssues($tasks);

        $this->io->block('Merge requests will be merged', null, 'fg=green');
        $this->listMergeRequests($tasks->mergeRequestsToMerge());

        $confirmed = $this->io->confirm('OK');

        if (!$confirmed) {
            $this->abort();
        }


        dump($tasks);


















//        $this->commandBus->dispatch(new CreateReleaseCommand($releaseBranchName));





        return Command::SUCCESS;
    }

    private function newReleaseBranchName(): Name
    {
        $this->io->section('Fetching latest release');

        /** @var Version $release */
        $release = $this->queryBus->ask(new GetLatestReleaseQuery());

        if (null === $release) {
            throw new \UnexpectedValueException('No release found');
        }

        if (!$release->released) {
            throw new \UnexpectedValueException("Latest release $release->name not yet released");
        }

        $latestReleaseBranchName = Name::fromString((string) $release->name);

        $this->io->info("Latest release branch name: $latestReleaseBranchName");

        return $this->io->ask(
            question: 'New release branch name',
            default: (string) $latestReleaseBranchName->bumpVersion(),
            validator: function (string $branchName) use ($latestReleaseBranchName): Name {
                $branchName = Name::fromString($branchName);

                if (!$branchName->versionNewerThan($latestReleaseBranchName)) {
                    throw new \InvalidArgumentException("Provided version must be greater than latest release $latestReleaseBranchName version");
                }

                return $branchName;
            },
        );
    }

    private function readyToMergeTasks(): IssueList
    {
        $this->io->section('Fetching Ready to Merge tasks');

        /** @var IssueList $tasks */
        $tasks = $this->queryBus->ask(new GetReadyToMergeTasksInActiveSprintQuery());

        if ($tasks->empty()) {
            $this->io->info('No Ready to Merge tasks found in the active sprint.');

            return $tasks;
        }

        $this->listIssues($tasks);

        $tasks = $tasks->filter(
            fn (Issue $task): bool => $this->io->confirm( "Add $task->key to the new release"),
        );

        $this->io->info('Tasks in the new release');

        $this->listIssues($tasks);

        return $tasks;
    }

    private function taskMergeRequests(Issue $task): MergeRequestList
    {
        $this->io->section("Fetching Merge Requests for $task");

        do {
            /** @var MergeRequestList $mergeRequests */
            $mergeRequests = $this->queryBus->ask(new GetIssueMergeRequestsQuery($task->id));

            if (!$mergeRequests->empty()) {
                break;
            }

            $this->io->note("No Merge Requests for $task found.");

            $action = $this->io->choice(
                question: 'Chose what to do',
                choices: array_keys(self::NO_MERGE_REQUESTS_ACTIONS),
                default: self::NO_MERGE_REQUESTS_ACTION_IDS['ABORT'],
            );

            $this->userChoices['NO_MERGE_REQUESTS_ACTION'] = self::NO_MERGE_REQUESTS_ACTIONS[$action];

            switch ($this->userChoices['NO_MERGE_REQUESTS_ACTION']) {
                case self::NO_MERGE_REQUESTS_ACTION_IDS['CONTINUE']:
                    return $mergeRequests;

                case self::NO_MERGE_REQUESTS_ACTION_IDS['ABORT']:
                    $this->abort();
            }
        } while (self::NO_MERGE_REQUESTS_ACTION_IDS['RELOAD'] === $this->userChoices['NO_MERGE_REQUESTS_ACTION']);

        $this->io->info("Merge Requests for $task");

        $this->listMergeRequests($mergeRequests);

        return $mergeRequests;
    }

    private function mergeRequestDetails(MergeRequest $mergeRequest): Details
    {
        $this->io->section("Fetching Details for $mergeRequest->projectName!$mergeRequest->id");

        do {
            /** @var Details $details */
            $details = $this->queryBus->ask(new GetMergeRequestDetailsQuery($mergeRequest->projectId, $mergeRequest->id));

            if ($details->mergeable()) {
                break;
            }

            $this->io->warning([
                "Merge request $mergeRequest->projectName!$mergeRequest->id is not mergeable.",
                "Merge status: {$mergeRequest->status->value}",
                $mergeRequest->guiUrl,
            ]);

            $confirmed = $this->io->confirm(
                question: "Check merge request $mergeRequest->guiUrl merge status again",
                default: false,
            );

            if (!$confirmed) {
                $this->abort();
            }
        } while (true);

        return $details;
    }

    private function taskMergeRequestsToMerge(
        Issue $task,
        BasicBranchName $targetBranchName,
    ): MergeRequestList {
        $mergeRequests = $task->mergeRequests
            ->targetToBranch($targetBranchName)
            ->relevantToSourceBranch($task->canonicalBranchName())
            ->filter(function (MergeRequest $mr): bool {
                /** @var bool $supported */
                $supported = $this->queryBus->ask(new GetProjectSupportedQuery($mr->projectId));

                return $supported;
            })
        ;

        if ($mergeRequests->empty()) {
            $this->io->note("No supported relevant outgoing Merge Requests for $task found.");
            $this->io->confirm('Continue', false);

            return $mergeRequests;
        }

        $mergeRequests = $mergeRequests->awaitingToMerge();
        $onlyOneMergeRequest = 1 === count($mergeRequests);

        $mergeRequests = $mergeRequests->filter(
            fn (MergeRequest $mr): bool => $this->io->confirm(
                question: "Merge $mr->projectName: $mr->sourceBranchName -> $mr->targetBranchName | $mr->name",
                default: $onlyOneMergeRequest || $mr->sourceEquals($task->canonicalBranchName()),
            ),
        );

        if ($mergeRequests->empty()) {
            $this->io->info("No Merge Requests for $task to merge");

            return $mergeRequests;
        }

        $this->io->info("Merge Requests $task to merge");

        $this->listMergeRequests($mergeRequests);

        return new MergeRequestList(
            ...$mergeRequests->map(function (MergeRequest $mr): MergeRequest {
                $details = $this->mergeRequestDetails($mr);

                return $mr->withDetails($details);
            }),
        );
    }

    private function listIssues(IssueList $tasks): void
    {
        $this->io->listing(iterator_to_array($tasks->map(
            fn (Issue $task): string => "{$this->issueGuiUrlFactory->createGuiUrl($task->key)} | $task->summary",
        )));
    }

    private function listMergeRequests(MergeRequestList $mergeRequests): void
    {
        $this->io->listing(iterator_to_array($mergeRequests->map(
            function (MergeRequest $mr): string {
                $fg = match ($mr->status) {
                    Status::Merged => 'green',
                    Status::Declined => 'gray',
                    Status::Open => 'bright-cyan',
                };

                return "<fg=$fg>[{$mr->status->value}]</> $mr->guiUrl $mr->sourceBranchName -> $mr->targetBranchName | $mr->name";
            },
        )));
    }

    private function taskWithMergeRequests(Issue $task): Issue
    {
        $mergeRequests = $this->taskMergeRequests($task);

        $task = $task->withMergeRequests($mergeRequests);

        if (self::NO_MERGE_REQUESTS_ACTION_IDS['CONTINUE'] === $this->userChoices['NO_MERGE_REQUESTS_ACTION']
            && $task->mergeRequests->empty()
        ) {
            return $task;
        }

        $mergeRequests = $this->taskMergeRequestsToMerge(
            task: $task,
            targetBranchName: BasicBranchName::fromString('develop'),
        );

        return $task->withMergeRequestsToMerge($mergeRequests);
    }

    private function abort(): void
    {
        throw new \RuntimeException('Aborted');
    }
}
