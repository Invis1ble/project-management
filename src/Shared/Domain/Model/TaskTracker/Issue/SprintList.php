<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Domain\Model\TaskTracker\Issue;

use ReleaseManagement\Shared\Domain\Model\AbstractList;
use ReleaseManagement\Shared\Domain\Model\TaskTracker\Board\BoardId;
use ReleaseManagement\Shared\Domain\Model\TaskTracker\Sprint\Sprint;

/**
 * @extends AbstractList<Sprint>
 */
final readonly class SprintList extends AbstractList
{
    private iterable $elements;

    public function __construct(Sprint ...$sprints)
    {
        $this->elements = $sprints;
    }

    public function containsActiveOnBoard(BoardId $boardId): bool
    {
        return $this->exists(
            fn (Sprint $sprint): bool => $sprint->active() && $sprint->boardId->equals($boardId),
        );
    }

    protected function elements(): iterable
    {
        return $this->elements;
    }
}
