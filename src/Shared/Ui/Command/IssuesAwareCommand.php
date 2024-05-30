<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Ui\Command;

use Invis1ble\Messenger\Command\CommandBusInterface;
use Invis1ble\Messenger\Query\QueryBusInterface;
use ProjectManagement\Shared\Application\UseCase\Query\GetIssueMergeRequests\GetIssueMergeRequestsQuery;
use ProjectManagement\Shared\Application\UseCase\Query\GetMergeRequestDetails\GetMergeRequestDetailsQuery;
use ProjectManagement\Shared\Application\UseCase\Query\GetProjectSupported\GetProjectSupportedQuery;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Details\Details;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequest;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestList;
use ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\Status;
use ProjectManagement\Shared\Domain\Model\SourceCodeRepository\Branch\Name as BasicBranchName;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\GuiUrlFactoryInterface;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\Issue;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

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

    protected function enrichIssuesWithMergeRequests(IssueList $issues): IssueList
    {
        return new IssueList(
            ...$issues->map(function (Issue $issue): Issue {
                $mergeRequests = $this->issueMergeRequests($issue);

                $issue = $issue->withMergeRequests($mergeRequests);

                if (self::NO_MERGE_REQUESTS_ACTION_IDS['CONTINUE'] === $this->userChoices['NO_MERGE_REQUESTS_ACTION']
                    && $issue->mergeRequests->empty()
                ) {
                    return $issue;
                }

                $mergeRequests = $this->issueMergeRequestsToMerge(
                    issue: $issue,
                    targetBranchName: BasicBranchName::fromString('master'),
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
                    Status::Open => 'bright-cyan',
                };

                return "<fg=$fg>[{$mr->status->value}]</> $mr->guiUrl $mr->sourceBranchName -> $mr->targetBranchName | $mr->title";
            },
        )));
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

    protected function abort(): void
    {
        throw new \RuntimeException('Aborted');
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
        $this->phase("Fetching Details for $mergeRequest->projectName!$mergeRequest->id...");

        while (true) {
            /** @var Details $details */
            $details = $this->queryBus->ask(new GetMergeRequestDetailsQuery($mergeRequest->projectId, $mergeRequest->id));

            if ($details->mergeable()) {
                break;
            }

            $this->io->warning([
                "Merge request $mergeRequest->projectName!$mergeRequest->id is not mergeable.",
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
                question: "Merge $mr->projectName: $mr->sourceBranchName -> $mr->targetBranchName | $mr->title",
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
