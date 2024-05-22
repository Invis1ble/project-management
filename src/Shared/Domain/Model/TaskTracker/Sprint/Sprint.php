<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Domain\Model\TaskTracker\Sprint;

use ProjectManagement\Shared\Domain\Model\TaskTracker\Board\BoardId;

final readonly class Sprint
{
    public function __construct(
        public BoardId $boardId,
        public Name $name,
        public State $state,
    ) {
    }

    public function active(): bool
    {
        return State::Active === $this->state;
    }
}
