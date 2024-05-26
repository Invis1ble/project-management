<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Infrastructure\Domain\Model\TaskTracker\Sprint;

use ProjectManagement\Shared\Domain\Model\TaskTracker\Board\BoardId;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Sprint\Name;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Sprint\Sprint;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Sprint\SprintFactoryInterface;
use ProjectManagement\Shared\Domain\Model\TaskTracker\Sprint\State;

final readonly class SprintFactory implements SprintFactoryInterface
{
    public function createSprint(
        int $boardId,
        string $name,
        string $state,
    ): Sprint {
        return new Sprint(
            boardId: BoardId::from($boardId),
            name: Name::fromString($name),
            state: State::from($state),
        );
    }
}
