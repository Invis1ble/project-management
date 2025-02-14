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
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch\Name as BasicBranchName;
use Invis1ble\ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Tag;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\SerializerInterface;

abstract class PublicationAwareCommand extends Command
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
        protected readonly SerializerInterface $serializer,
        protected readonly Issue\StatusProviderInterface $issueStatusProvider,
        protected readonly Issue\GuiUrlFactoryInterface $issueGuiUrlFactory,
        protected readonly \DateInterval $pipelineMaxAwaitingTime,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();

        $this
            ->addOption(
                name: 'resume',
                shortcut: null,
                mode: InputOption::VALUE_OPTIONAL,
                description: 'Resume saga with an optional publication id.',
                default: false,
            )
            ->addOption(
                name: 'dry-run',
                shortcut: null,
                mode: InputOption::VALUE_NONE,
                description: 'Execute the command as a dry run.',
            )
        ;
    }

    protected function listIssues(Issue\IssueList $issues): void
    {
        $this->listing(iterator_to_array($issues->map(
            fn (Issue\Issue $task): string => "{$this->issueGuiUrlFactory->createGuiUrl($task->key)} | $task->summary",
        )));
    }

    protected function enrichIssuesWithMergeRequests(
        Issue\IssueList $issues,
        BasicBranchName $targetBranchName,
    ): Issue\IssueList {
        return new Issue\IssueList(
            ...$issues->map(function (Issue\Issue $issue) use ($targetBranchName): Issue\Issue {
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

    protected function listMergeRequests(MergeRequest\MergeRequestList $mergeRequests): void
    {
        $this->listing(iterator_to_array($mergeRequests->map(
            function (MergeRequest\MergeRequest $mr): string {
                $fg = match ($mr->status) {
                    MergeRequest\Status::Merged => 'green',
                    MergeRequest\Status::Declined => 'gray',
                    MergeRequest\Status::Open => 'cyan',
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

        $tagName = $this->io->ask(
            question: 'New tag',
            default: (string) $tagName,
        );

        return Tag\VersionName::fromString($tagName);
    }

    protected function showProgressLog(QueryInterface $query, callable $inFinalState): int
    {
        $untilTime = (new \DateTimeImmutable())->add($this->pipelineMaxAwaitingTime);
        $tickInterval = 10;
        $previousStatus = null;
        $statusChanged = false;

        while (new \DateTimeImmutable() <= $untilTime) {
            $publication = $this->getPublication($query);
            $status = $publication->status();
            $this->displayProgress($this->serializer->serialize($status, 'json'));

            if (null === $previousStatus || ($statusChanged = !$status->equals($previousStatus))) {
                $previousStatus = $status;
            }

            if ($inFinalState($publication)) {
                return Command::SUCCESS;
            }

            sleep($tickInterval);

            if ($statusChanged) {
                $untilTime = (new \DateTimeImmutable())->add($this->pipelineMaxAwaitingTime);
            }
        }

        if (null === $previousStatus || !$statusChanged) {
            $this->abort('Publication stuck');
        }

        return Command::SUCCESS;
    }

    abstract protected function getPublication(QueryInterface $query): ReleasePublicationInterface|HotfixPublicationInterface;

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

    protected function time(): string
    {
        return date('d.m.Y H:i:sP');
    }

    private function issueMergeRequests(Issue\Issue $issue): MergeRequest\MergeRequestList
    {
        $this->phase("Fetching Merge Requests for $issue...");

        do {
            /** @var MergeRequest\MergeRequestList $mergeRequests */
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

    private function mergeRequestDetails(MergeRequest\MergeRequest $mergeRequest): MergeRequest\Details\Details
    {
        $this->phase("Fetching Details for $mergeRequest->projectName!$mergeRequest->iid...");

        $autoRetriesLimit = 3;
        $retryNumber = 0;

        while (true) {
            /** @var MergeRequest\Details\Details $details */
            $details = $this->queryBus->ask(new GetMergeRequestDetailsQuery($mergeRequest->projectId, $mergeRequest->iid));

            if ($details->mergeable()) {
                break;
            }

            ++$retryNumber;

            $this->io->warning([
                "Merge request $mergeRequest->projectName!$mergeRequest->iid is not mergeable.",
                "Merge request status: $details->status",
                $mergeRequest->guiUrl,
            ]);

            $start = time();
            $retryTimeout = 10;

            if ($retryNumber >= $autoRetriesLimit) {
                $confirmed = $this->io->confirm(
                    question: "Check merge request $mergeRequest->guiUrl merge status again in $retryTimeout seconds",
                    default: $details->mayBeMergeable(),
                );
            } else {
                $confirmed = true;
            }

            if ($confirmed) {
                $passed = time() - $start;

                if ($passed < $retryTimeout) {
                    sleep($retryTimeout - $passed);
                }

                $this->phase("Retry #$retryNumber to fetch Details for $mergeRequest->projectName!$mergeRequest->iid...");
            } else {
                $this->abort();
            }
        }

        return $details;
    }

    private function issueMergeRequestsToMerge(
        Issue\Issue $issue,
        BasicBranchName $targetBranchName,
    ): MergeRequest\MergeRequestList {
        $mergeRequests = $issue->mergeRequests
            ->targetToBranch($targetBranchName)
            ->relevantToSourceBranch($issue->canonicalBranchName())
            ->filter(function (MergeRequest\MergeRequest $mr): bool {
                if (!$mr->open()) {
                    return false;
                }

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
            fn (MergeRequest\MergeRequest $mr): bool => $this->io->confirm(
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

        return new MergeRequest\MergeRequestList(
            ...$mergeRequests->map(function (MergeRequest\MergeRequest $mr): MergeRequest\MergeRequest {
                $details = $this->mergeRequestDetails($mr);

                return $mr->withDetails($details);
            }),
        );
    }
}
