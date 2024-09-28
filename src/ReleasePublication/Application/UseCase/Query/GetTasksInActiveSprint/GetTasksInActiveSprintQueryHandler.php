<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\ReleasePublication\Application\UseCase\Query\GetTasksInActiveSprint;

use Invis1ble\Messenger\Query\QueryHandlerInterface;
use Invis1ble\ProjectManagement\ReleasePublication\Domain\Model\TaskTracker\TaskTrackerInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;

final readonly class GetTasksInActiveSprintQueryHandler implements QueryHandlerInterface
{
    public function __construct(private TaskTrackerInterface $taskTracker)
    {
    }

    public function __invoke(GetTasksInActiveSprintQuery $query): IssueList
    {
        return $this->taskTracker->tasksInActiveSprint(...$query->statuses);
    }
}
