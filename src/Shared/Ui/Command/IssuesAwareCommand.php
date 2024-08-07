<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Ui\Command;

use Invis1ble\Messenger\Command\CommandBusInterface;
use Invis1ble\Messenger\Query\QueryBusInterface;
use Invis1ble\Messenger\Query\QueryInterface;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\HotfixPublicationInterface;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\ReleasePublicationInterface;
use Invis1ble\ProjectManagement\Shared\Application\UseCase\Query\GetIssueMergeRequests\GetIssueMergeRequestsQuery;
use Invis1ble\ProjectManagement\Shared\Application\UseCase\Query\GetLatestTagToday\GetLatestTagTodayQuery;
use Invis1ble\ProjectManagement\Shared\Application\UseCase\Query\GetMergeRequestDetails\GetMergeRequestDetailsQuery;
use Invis1ble\ProjectManagement\Shared\Application\UseCase\Query\GetProjectSupported\GetProjectSupportedQuery;
use Invis1ble\ProjectManagement\Shared\Domain\Exception\PublicationNotFoundException;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Details;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequest;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestList;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Status;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch\Name as BasicBranchName;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\GuiUrlFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\Issue;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\SerializerInterface;

abstract class IssuesAwareCommand extends Command
{
    protected SymfonyStyle $io;

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
        protected readonly QueryBusInterface $queryBus,
        protected readonly CommandBusInterface $commandBus,
        protected readonly GuiUrlFactoryInterface $issueGuiUrlFactory,
        protected readonly SerializerInterface $serializer,
        protected readonly \DateInterval $pipelineMaxAwaitingTime,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();

        $this
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Execute the command as a dry run.',
            )
        ;
    }

    protected function listIssues(IssueList $issues): void
    {
        $this->listing(iterator_to_array($issues->map(
            fn (Issue $task): string => "{$this->issueGuiUrlFactory->createGuiUrl($task->key)} | $task->summary",
        )));
    }

    protected function enrichIssuesWithMergeRequests(IssueList $issues, BasicBranchName $targetBranchName): IssueList
    {
        return new IssueList(
            ...$issues->map(function (Issue $issue) use ($targetBranchName): Issue {
                $mergeRequests = $this->issueMergeRequests($issue);

                $issue = $issue->withMergeRequests($mergeRequests);

                if (self::NO_MERGE_REQUESTS_ACTION_IDS['CONTINUE'] === $this->userChoices['NO_MERGE_REQUESTS_ACTION']
                    && $issue->mergeRequests->empty()
                ) {
                    return $issue;
                }

                $mergeRequests = $this->issueMergeRequestsToMerge(
                    issue: $issue,
                    targetBranchName: $targetBranchName,
                );

                return $issue->withMergeRequestsToMerge($mergeRequests);
            }),
        );
    }

    protected function listMergeRequests(MergeRequestList $mergeRequests): void
    {
        $this->listing(iterator_to_array($mergeRequests->map(
            function (MergeRequest $mr): string {
                $fg = match ($mr->status) {
                    Status::Merged => 'green',
                    Status::Declined => 'gray',
                    Status::Open => 'cyan',
                };

                return "<bg=$fg;fg=black;options=bold> {$mr->status->value} </> $mr->guiUrl <options=bold>$mr->sourceBranchName -> $mr->targetBranchName</> | $mr->title";
            },
        )));
    }

    protected function newTagName(): Tag\VersionName
    {
        $this->phase('Calculating new tag today...');

        /** @var ?Tag\Tag<Tag\VersionName> $latestTagToday */
        $latestTagToday = $this->queryBus->ask(new GetLatestTagTodayQuery());

        $this->caption('Latest tag today');

        if (null === $latestTagToday) {
            $this->io->block('No tags today');
        } else {
            $this->io->block((string) $latestTagToday->name);
        }

        $this->caption('New calculated tag');

        if (null === $latestTagToday) {
            $tagName = Tag\VersionName::create();
        } else {
            $tagName = Tag\VersionName::fromRef($latestTagToday->name)->bumpVersion();
        }

        $this->io->block((string) $tagName);

        return $this->io->ask(
            question: 'New tag',
            default: (string) $tagName,
            validator: function (string $answer) use ($latestTagToday): Tag\VersionName {
                $tagName = Tag\VersionName::fromString($answer);

                if (null === $latestTagToday) {
                    return $tagName;
                }

                if (!$tagName->versionNewerThan(Tag\VersionName::fromRef($latestTagToday->name))) {
                    throw new \InvalidArgumentException("Provided version must be greater than latest tag $latestTagToday->name version");
                }

                return $tagName;
            },
        );
    }

    protected function showProgressLog(QueryInterface $query, callable $inFinalState): int
    {
        $startTime = new \DateTimeImmutable();
        $untilTime = $startTime->add($this->pipelineMaxAwaitingTime);
        $tickInterval = 10;
        $previousStatus = null;
        $statusChanged = false;

        $getPublicationMaxTries = 3;
        $retryCounter = 0;

        while (new \DateTimeImmutable() <= $untilTime) {
            try {
                /** @var HotfixPublicationInterface|ReleasePublicationInterface $publication */
                $publication = $this->queryBus->ask($query);
            } catch (PublicationNotFoundException) {
                // publication is not created, await async handlers
                sleep(3);
                ++$retryCounter;

                if ($retryCounter >= $getPublicationMaxTries) {
                    break;
                }

                continue;
            }

            $status = $publication->status();
            $this->displayProgress($this->serializer->serialize($status, 'json'));

            if (null === $previousStatus || ($statusChanged = !$status->equals($previousStatus))) {
                $previousStatus = $status;
            }

            if ($inFinalState($publication)) {
                return Command::SUCCESS;
            }

            sleep($tickInterval);

            if (isset($statusChanged) && $statusChanged) {
                $untilTime = (new \DateTimeImmutable())->add($this->pipelineMaxAwaitingTime);
            }
        }

        if (null === $previousStatus || !$statusChanged) {
            $this->abort('Publication stuck');
        }

        return Command::SUCCESS;
    }

    protected function displayProgress(string $message): void
    {
        $this->io->writeln('[' . date('Y-m-d\TH:i:s.uP') . "] $message");
    }

    protected function caption(string $text): void
    {
        $this->io->block($text, null, 'fg=green');
    }

    protected function phase(string $text): void
    {
        $this->io->block($text, null, 'fg=gray');
    }

    protected function listing(array $elements): void
    {
        $this->io->listing($elements);
    }

    protected function abort(string $message = 'Aborted'): void
    {
        throw new \RuntimeException($message);
    }

    private function issueMergeRequests(Issue $issue): MergeRequestList
    {
        $this->phase("Fetching Merge Requests for $issue...");

        do {
            /** @var MergeRequestList $mergeRequests */
            $mergeRequests = $this->queryBus->ask(new GetIssueMergeRequestsQuery($issue->id));

            if (!$mergeRequests->empty()) {
                break;
            }

            $this->io->note("No Merge Requests for $issue found.");

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

        $this->listMergeRequests($mergeRequests);

        return $mergeRequests;
    }

    private function mergeRequestDetails(MergeRequest $mergeRequest): Details
    {
        $this->phase("Fetching Details for $mergeRequest->projectName!$mergeRequest->iid...");

        while (true) {
            /** @var Details $details */
            $details = $this->queryBus->ask(new GetMergeRequestDetailsQuery($mergeRequest->projectId, $mergeRequest->iid));

            if ($details->mergeable()) {
                break;
            }

            $this->io->warning([
                "Merge request $mergeRequest->projectName!$mergeRequest->iid is not mergeable.",
                "Merge request status: $details->status",
                $mergeRequest->guiUrl,
            ]);

            $retryTimeout = 10;

            $confirmed = $this->io->confirm(
                question: "Check merge request $mergeRequest->guiUrl merge status again in $retryTimeout seconds",
                default: $details->mayBeMergeable(),
            );

            if ($confirmed) {
                sleep($retryTimeout);
            } else {
                $this->abort();
            }
        }

        return $details;
    }

    private function issueMergeRequestsToMerge(
        Issue $issue,
        BasicBranchName $targetBranchName,
    ): MergeRequestList {
        $mergeRequests = $issue->mergeRequests
            ->targetToBranch($targetBranchName)
            ->relevantToSourceBranch($issue->canonicalBranchName())
            ->filter(function (MergeRequest $mr): bool {
                /** @var bool $supported */
                $supported = $this->queryBus->ask(new GetProjectSupportedQuery($mr->projectId));

                return $supported;
            })
        ;

        if ($mergeRequests->empty()) {
            $this->io->note("No supported relevant outgoing Merge Requests for $issue found.");
            $this->io->confirm('Continue', false);

            return $mergeRequests;
        }

        $mergeRequests = $mergeRequests->awaitingToMerge();
        $onlyOneMergeRequest = 1 === count($mergeRequests);

        $mergeRequests = $mergeRequests->filter(
            fn (MergeRequest $mr): bool => $this->io->confirm(
                question: "Merge $mr->projectName!$mr->iid: $mr->sourceBranchName -> $mr->targetBranchName | $mr->title",
                default: $onlyOneMergeRequest || $mr->sourceEquals($issue->canonicalBranchName()),
            ),
        );

        if ($mergeRequests->empty()) {
            $this->caption("No Merge Requests to merge for $issue");

            return $mergeRequests;
        }

        $this->caption("Merge Requests to merge for $issue");

        $this->listMergeRequests($mergeRequests);

        return new MergeRequestList(
            ...$mergeRequests->map(function (MergeRequest $mr): MergeRequest {
                $details = $this->mergeRequestDetails($mr);

                return $mr->withDetails($details);
            }),
        );
    }
}
