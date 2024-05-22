<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Infrastructure\Domain\Model\TaskTracker\Issue;

use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\Issue;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueFactoryInterface;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\IssueId;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\Key;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\SprintList;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\Summary;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Issue\TypeId;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Sprint\Sprint;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Sprint\SprintFactoryInterface;

final readonly class IssueFactory implements IssueFactoryInterface
{
    public function __construct(private SprintFactoryInterface $sprintFactory)
    {
    }

    public function createIssue(
        int $id,
        string $key,
        string $typeId,
        string $summary,
        array $sprints,
    ): Issue {
        return new Issue(
            id: IssueId::from($id),
            key: Key::fromString($key),
            typeId: TypeId::fromString($typeId),
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
