<?php

declare(strict_types=1);

namespace ReleaseManagement\ReleasePublication\Application\UseCase\Query\GetReadyToMergeTasksInActiveSprint;

use Invis1ble\Messenger\Query\QueryHandlerInterface;
use ReleaseManagement\ReleasePublication\Domain\Model\TaskTracker\TaskTrackerInterface;
use ReleaseManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;

final readonly class GetReadyToMergeTasksInActiveSprintQueryHandler implements QueryHandlerInterface
{
    public function __construct(private TaskTrackerInterface $taskTracker)
    {
    }

    public function __invoke(GetReadyToMergeTasksInActiveSprintQuery $query): IssueList
    {
        return $this->taskTracker->readyToMergeTasksInActiveSprint();
    }
}
