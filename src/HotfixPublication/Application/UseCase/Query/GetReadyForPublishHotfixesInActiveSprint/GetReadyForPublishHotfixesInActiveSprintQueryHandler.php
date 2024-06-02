<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\HotfixPublication\Application\UseCase\Query\GetReadyForPublishHotfixesInActiveSprint;

use Invis1ble\Messenger\Query\QueryHandlerInterface;
use Invis1ble\ProjectManagement\HotfixPublication\Domain\Model\TaskTracker\TaskTrackerInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueList;

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
