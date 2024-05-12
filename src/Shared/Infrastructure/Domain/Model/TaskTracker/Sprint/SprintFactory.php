<?php

declare(strict_types=1);

namespace ReleaseManagement\Shared\Infrastructure\Domain\Model\TaskTracker\Sprint;

use ReleaseManagement\Shared\Domain\Model\TaskTracker\Board\BoardId;
use ReleaseManagement\Shared\Domain\Model\TaskTracker\Sprint\Name;
use ReleaseManagement\Shared\Domain\Model\TaskTracker\Sprint\Sprint;
use ReleaseManagement\Shared\Domain\Model\TaskTracker\Sprint\SprintFactoryInterface;
use ReleaseManagement\Shared\Domain\Model\TaskTracker\Sprint\State;

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
