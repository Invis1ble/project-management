<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Application\UseCase\Query\GetHotfixesInActiveSprint;

use Invis1ble\Messenger\Query\QueryHandlerInterface;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\TaskTracker\TaskTrackerInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;

final readonly class GetHotfixesInActiveSprintQueryHandler implements QueryHandlerInterface
{
    public function __construct(private TaskTrackerInterface $taskTracker)
    {
    }

    public function __invoke(GetHotfixesInActiveSprintQuery $query): IssueList
    {
        return $this->taskTracker->readyForPublishHotfixes(
            keys: $query->keys,
            statuses: $query->statuses,
        );
    }
}
