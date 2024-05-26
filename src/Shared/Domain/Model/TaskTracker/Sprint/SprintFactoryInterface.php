<?php

declare(strict_types=1);

namespace ProjectManagement\Shared\Domain\Model\TaskTracker\Sprint;

interface SprintFactoryInterface
{
    public function createSprint(
        int $boardId,
        string $name,
        string $state,
    ): Sprint;
}
