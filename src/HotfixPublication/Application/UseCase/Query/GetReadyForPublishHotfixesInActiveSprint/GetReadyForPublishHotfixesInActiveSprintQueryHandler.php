<?php

declare(strict_types=1);

namespace ProjectManagement\HotfixPublication\Application\UseCase\Query\GetReadyForPublishHotfixesInActiveSprint;

use Invis1ble\Messenger\Query\QueryHandlerInterface;
use ProjectManagement\HotfixPublication\Domain\Model\TaskTracker\TaskTrackerInterface;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;

final readonly class GetReadyForPublishHotfixesInActiveSprintQueryHandler implements QueryHandlerInterface
{
    public function __construct(private TaskTrackerInterface $taskTracker)
    {
    }

    public function __invoke(GetReadyForPublishHotfixesInActiveSprintQuery $query): IssueList
    {
        return $this->taskTracker->readyForPublishHotfixes(...$query->keys);
    }
}
