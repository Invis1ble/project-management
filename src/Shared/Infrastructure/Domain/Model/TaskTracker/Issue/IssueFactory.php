<?php

declare(strict_types=1);

namespace Invis1ble\ProjectManagement\Shared\Infrastructure\Domain\Model\TaskTracker\Issue;

use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\Issue;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueId;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\Key;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\Summary;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\TypeId;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Sprint\Sprint;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Sprint\SprintFactoryInterface;
use Invis1ble\ProjectManagement\Shared\Domain\Model\TaskTracker\Sprint\SprintList;

final readonly class IssueFactory implements IssueFactoryInterface
{
    public function __construct(private SprintFactoryInterface $sprintFactory)
    {
    }

    public function createIssue(
        int $id,
        string $key,
        string $typeId,
        bool $subtask,
        string $summary,
        array $sprints,
    ): Issue {
        return new Issue(
            id: IssueId::from($id),
            key: Key::fromString($key),
            typeId: TypeId::fromString($typeId),
            subtask: $subtask,
            summary: Summary::fromString($summary),
            sprints: new SprintList(
                ...array_map(
                    callback: fn (array $sprint): Sprint => $this->sprintFactory->createSprint(
                        $sprint['boardId'],
                        $sprint['name'],
                        $sprint['state'],
                    ),
                    array: $sprints,
                ),
            ),
            mergeRequests: null,
            mergeRequestsToMerge: null,
        );
    }
}
