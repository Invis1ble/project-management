<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Application\UseCase\Query\GetIssueMergeRequests;

use Invis1ble\Messenger\Query\QueryHandlerInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\DevelopmentCollaboration\MergeRequest\MergeRequestList;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\TaskTrackerInterface;

final readonly class GetIssueMergeRequestsQueryHandler implements QueryHandlerInterface
{
    public function __construct(private TaskTrackerInterface $taskTracker)
    {
    }

    public function __invoke(GetIssueMergeRequestsQuery $query): MergeRequestList
    {
        return $this->taskTracker->mergeRequestsRelatedToIssue($query->issueId);
    }
}
